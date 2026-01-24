<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCacheService
{
    private const CACHE_TTL = 300; // 300 seconds (5 minutes) as per TODO
    private const ONLINE_STATUS_TTL = 60; // 60 seconds for online status

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
     * Fetch customers from database.
     */
    private function fetchCustomers(int $tenantId): Collection
    {
        try {
            return NetworkUser::where('tenant_id', $tenantId)
                ->with([
                    'package:id,name,price,bandwidth_download,bandwidth_upload',
                    'user:id,name,mobile,email,zone_id,created_at',
                    'user.zone:id,name',
                ])
                ->select([
                    'id',
                    'user_id',
                    'tenant_id',
                    'username',
                    'package_id',
                    'status',
                    'connection_type',
                    'billing_type',
                    'device_type',
                    'mac_address',
                    'ip_address',
                    'is_active',
                    'created_at',
                    'updated_at',
                ])
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
     */
    private function fetchOnlineStatus(array $customerIds): array
    {
        if (empty($customerIds)) {
            return [];
        }

        try {
            // Query radacct table for active sessions using radius connection
            // Note: This assumes you have a radacct table. Adjust based on your schema.
            $activeSessions = DB::connection('radius')->table('radacct')
                ->select('username')
                ->whereIn('username', function ($query) use ($customerIds) {
                    $query->select('username')
                        ->from('network_users')
                        ->whereIn('id', $customerIds);
                })
                ->whereNull('acctstoptime')
                ->get()
                ->pluck('username')
                ->unique()
                ->toArray();

            // Convert usernames back to customer IDs
            $onlineCustomers = DB::table('network_users')
                ->select('id')
                ->whereIn('username', $activeSessions)
                ->pluck('id')
                ->toArray();

            // Create status array
            $statusArray = [];
            foreach ($customerIds as $customerId) {
                $statusArray[$customerId] = in_array($customerId, $onlineCustomers);
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
