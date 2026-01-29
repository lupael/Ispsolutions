<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Operator Statistics Cache Service
 * 
 * Provides caching for operator statistics and performance metrics
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #4
 */
class OperatorStatsCacheService
{
    private const CACHE_TTL = 300; // 5 minutes for dashboard and customer stats
    private const SHORT_TTL = 60; // 1 minute for revenue statistics (frequently changing)

    /**
     * Get cached operator dashboard statistics
     * 
     * @param int $operatorId The operator user ID
     * @param bool $refresh Whether to refresh the cache
     * @return array
     */
    public function getDashboardStats(int $operatorId, bool $refresh = false): array
    {
        $cacheKey = "operator_stats:dashboard:{$operatorId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($operatorId) {
            return $this->calculateDashboardStats($operatorId);
        });
    }

    /**
     * Get cached customer statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @param bool $refresh Whether to refresh the cache
     * @return array
     */
    public function getCustomerStats(int $operatorId, bool $refresh = false): array
    {
        $cacheKey = "operator_stats:customers:{$operatorId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($operatorId) {
            return $this->calculateCustomerStats($operatorId);
        });
    }

    /**
     * Get cached revenue statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @param bool $refresh Whether to refresh the cache
     * @return array
     */
    public function getRevenueStats(int $operatorId, bool $refresh = false): array
    {
        $cacheKey = "operator_stats:revenue:{$operatorId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::SHORT_TTL, function () use ($operatorId) {
            return $this->calculateRevenueStats($operatorId);
        });
    }

    /**
     * Invalidate all cached statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @return void
     */
    public function invalidateCache(int $operatorId): void
    {
        Cache::forget("operator_stats:dashboard:{$operatorId}");
        Cache::forget("operator_stats:customers:{$operatorId}");
        Cache::forget("operator_stats:revenue:{$operatorId}");
    }

    /**
     * Calculate dashboard statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @return array
     */
    private function calculateDashboardStats(int $operatorId): array
    {
        try {
            $operator = User::find($operatorId);
            if (!$operator) {
                return $this->getEmptyStats();
            }

            $tenantId = $operator->tenant_id;

            $stats = [
                'total_customers' => User::where('tenant_id', $tenantId)
                    ->where('operator_level', 100)
                    ->count(),
                
                'active_customers' => User::where('tenant_id', $tenantId)
                    ->where('operator_level', 100)
                    ->where('status', 'active')
                    ->count(),
                
                'suspended_customers' => User::where('tenant_id', $tenantId)
                    ->where('operator_level', 100)
                    ->where('status', 'suspended')
                    ->count(),
                
                'expired_customers' => User::where('tenant_id', $tenantId)
                    ->where('operator_level', 100)
                    ->where('status', 'expired')
                    ->count(),
                
                'timestamp' => now()->toDateTimeString(),
            ];

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to calculate operator dashboard stats', [
                'operator_id' => $operatorId,
                'error' => $e->getMessage(),
            ]);

            return $this->getEmptyStats();
        }
    }

    /**
     * Calculate customer statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @return array
     */
    private function calculateCustomerStats(int $operatorId): array
    {
        try {
            $operator = User::find($operatorId);
            if (!$operator) {
                return [];
            }

            $tenantId = $operator->tenant_id;

            // Get customer counts by status
            $byStatus = User::where('tenant_id', $tenantId)
                ->where('operator_level', 100)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get customer counts by payment type
            $byPaymentType = User::where('tenant_id', $tenantId)
                ->where('operator_level', 100)
                ->select('payment_type', DB::raw('count(*) as count'))
                ->groupBy('payment_type')
                ->pluck('count', 'payment_type')
                ->toArray();

            return [
                'by_status' => $byStatus,
                'by_payment_type' => $byPaymentType,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate customer stats', [
                'operator_id' => $operatorId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Calculate revenue statistics for an operator
     * 
     * @param int $operatorId The operator user ID
     * @return array
     */
    private function calculateRevenueStats(int $operatorId): array
    {
        try {
            $operator = User::find($operatorId);
            if (!$operator) {
                return [];
            }

            $tenantId = $operator->tenant_id;

            // Calculate monthly recurring revenue (MRR)
            $mrr = User::where('users.tenant_id', $tenantId)
                ->where('users.operator_level', 100)
                ->where('users.status', 'active')
                ->where('users.payment_type', 'postpaid')
                ->join('packages', 'users.service_package_id', '=', 'packages.id')
                ->sum('packages.price');

            // Calculate prepaid active revenue
            $prepaidRevenue = User::where('users.tenant_id', $tenantId)
                ->where('users.operator_level', 100)
                ->where('users.status', 'active')
                ->where('users.payment_type', 'prepaid')
                ->join('packages', 'users.service_package_id', '=', 'packages.id')
                ->sum('packages.price');

            return [
                'monthly_recurring_revenue' => round($mrr, 2),
                'prepaid_revenue' => round($prepaidRevenue, 2),
                'total_revenue' => round($mrr + $prepaidRevenue, 2),
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate revenue stats', [
                'operator_id' => $operatorId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get empty statistics structure
     * 
     * @return array
     */
    private function getEmptyStats(): array
    {
        return [
            'total_customers' => 0,
            'active_customers' => 0,
            'suspended_customers' => 0,
            'expired_customers' => 0,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
