<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Schema;

class CustomerCacheService
{
    private const CACHE_TTL = 300; // 300 seconds (5 minutes) as per TODO
    private const ONLINE_STATUS_TTL = 60; // 60 seconds for online status
    private const COLUMN_CACHE_TTL = 3600; // 1 hour for column listing cache
    
    private static ?array $cachedColumns = null;

    /**
     * Get cached customer list.
     */
    public function getCustomers(int $tenantId, ?int $roleId = null, bool $refresh = false): Collection
    {
        $roleId = $roleId ?? 0; // Use 0 as default if roleId is null
        $cacheKey = "customers:tenant:{$tenantId}:role:{$roleId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            return $this->fetchCustomers($tenantId);
        });
    }

    /**
     * Get online status for customers.
     */
    public function getOnlineStatus(array $customerIds, bool $refresh = false): array
    {
        $cacheKey = "customers:online_status:" . md5(implode(',', $customerIds));

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::ONLINE_STATUS_TTL, function () use ($customerIds) {
            return $this->fetchOnlineStatus($customerIds);
        });
    }

    /**
     * Invalidate customer cache for a tenant.
     */
    public function invalidateCache(int $tenantId): void
    {
        // Clear all customer caches for this tenant
        $pattern = "customers:tenant:{$tenantId}:*";
        
        // Note: This is a simplified version. In production, you might want to use
        // Redis SCAN command or maintain a list of cache keys
        try {
            // If using Redis, you can use pattern-based deletion
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->connection();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate customer cache', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get available columns for users table (cached).
     */
    private function getAvailableColumns(): array
    {
        // Use static cache to avoid repeated database calls within the same request
        if (self::$cachedColumns === null) {
            // Use Laravel cache for cross-request caching
            self::$cachedColumns = Cache::remember(
                'users:available_columns',
                self::COLUMN_CACHE_TTL,
                fn() => Schema::getColumnListing('users')
            );
        }
        
        return self::$cachedColumns;
    }

    /**
     * Fetch customers from database.
     * Network credentials are now stored directly in the User model.
     */
    private function fetchCustomers(int $tenantId): Collection
    {
        try {
            // Build the select array dynamically based on available columns
            $selectColumns = [
                'id',
                'tenant_id',
                'name',
                'email',
                'mobile',
                'username',
                'name',
                'email',
                'phone',
                'service_package_id',
                'status',
                'operator_level',
            ];
            
            // Add columns only if they exist in the table
            $availableColumns = $this->getAvailableColumns();
            $optionalColumns = [
                'expiry_date',
                'connection_type',
                'service_type',
                'billing_type',
                'service_type',
                'device_type',
                'mac_address',
                'ip_address',
                'is_active',
                'zone_id',
                'created_at',
                'updated_at',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'wallet_balance',
            ];
            
            foreach ($optionalColumns as $column) {
                if (in_array($column, $availableColumns)) {
                    $selectColumns[] = $column;
                }
            }
            
            return User::where('tenant_id', $tenantId)
                ->where('is_subscriber', true) // Customers only
                ->with([
                    'package:id,name,price,bandwidth_down,bandwidth_up',
                    'zone:id,name',
                ])
                ->select($selectColumns)
                ->get()
                ->map(function ($customer) {
                    // Add computed attributes
                    $customer->online_status = false; // Will be populated separately
                    return $customer;
                });
        } catch (\Exception $e) {
            Log::error('Failed to fetch customers', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Fetch online status from radacct table.
     * 
     * Note: After NetworkUser migration, we now query users table for usernames.
     */
    private function fetchOnlineStatus(array $customerIds): array
    {
        if (empty($customerIds)) {
            return [];
        }

        try {
            // First, fetch usernames and create a mapping from the main database
            $usernameToId = DB::table('users')
                ->select('id', 'username')
                ->whereIn('id', $customerIds)
                ->where('is_subscriber', true)
                ->pluck('id', 'username')
                ->toArray();

            if (empty($usernameToId)) {
                return array_fill_keys($customerIds, false);
            }

            // Then, query radacct table for active sessions using radius connection
            $activeSessions = DB::connection('radius')->table('radacct')
                ->select('username')
                ->whereIn('username', array_keys($usernameToId))
                ->whereNull('acctstoptime')
                ->get()
                ->pluck('username')
                ->unique()
                ->toArray();

            // Convert usernames to customer IDs using the mapping
            $onlineCustomerIds = [];
            foreach ($activeSessions as $username) {
                if (isset($usernameToId[$username])) {
                    $onlineCustomerIds[] = $usernameToId[$username];
                }
            }

            // Create status array
            $statusArray = [];
            foreach ($customerIds as $customerId) {
                $statusArray[$customerId] = in_array($customerId, $onlineCustomerIds);
            }

            return $statusArray;
        } catch (\Exception $e) {
            Log::error('Failed to fetch online status', [
                'customer_ids' => $customerIds,
                'error' => $e->getMessage(),
            ]);

            // Return all offline if query fails
            return array_fill_keys($customerIds, false);
        }
    }

    /**
     * Attach online status to customers.
     */
    public function attachOnlineStatus(Collection $customers, bool $refresh = false): Collection
    {
        $customerIds = $customers->pluck('id')->toArray();
        if (empty($customerIds)) {
            return $customers;
        }

        $onlineStatus = $this->getOnlineStatus($customerIds, $refresh);

        return $customers->map(function ($customer) use ($onlineStatus) {
            $customer->online_status = $onlineStatus[$customer->id] ?? false;
            return $customer;
        });
    }
}
