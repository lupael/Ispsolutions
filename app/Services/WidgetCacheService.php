<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\SmsLog;
use App\Models\SubscriptionBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WidgetCacheService
{
    private const CACHE_TTL = 200; // 200 seconds (3 minutes 20 seconds) as per TODO

    /**
     * Get suspension forecast widget data with caching.
     */
    public function getSuspensionForecast(int $tenantId, bool $refresh = false): array
    {
        $cacheKey = "widget:suspension_forecast:tenant:{$tenantId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            return $this->calculateSuspensionForecast($tenantId);
        });
    }

    /**
     * Get collection target widget data with caching.
     */
    public function getCollectionTarget(int $tenantId, bool $refresh = false): array
    {
        $cacheKey = "widget:collection_target:tenant:{$tenantId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            return $this->calculateCollectionTarget($tenantId);
        });
    }

    /**
     * Get SMS usage widget data with caching.
     */
    public function getSmsUsage(int $tenantId, bool $refresh = false): array
    {
        $cacheKey = "widget:sms_usage:tenant:{$tenantId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            return $this->calculateSmsUsage($tenantId);
        });
    }

    /**
     * Refresh all widgets for a tenant.
     */
    public function refreshAllWidgets(int $tenantId): void
    {
        $this->getSuspensionForecast($tenantId, true);
        $this->getCollectionTarget($tenantId, true);
        $this->getSmsUsage($tenantId, true);
    }

    /**
     * Refresh specific widget for a tenant.
     */
    public function refreshWidget(int $tenantId, string $widgetName): ?array
    {
        return match ($widgetName) {
            'suspension_forecast' => $this->getSuspensionForecast($tenantId, true),
            'collection_target' => $this->getCollectionTarget($tenantId, true),
            'sms_usage' => $this->getSmsUsage($tenantId, true),
            default => null,
        };
    }

    /**
     * Calculate suspension forecast data.
     */
    private function calculateSuspensionForecast(int $tenantId): array
    {
        try {
            $today = Carbon::today();

            // Find users expiring today from network_users table
            // NOTE: Expiry logic uses expiry_date field on network_users
            // - Active users with status != 'suspended' and is_active = true are checked
            $usersAtRisk = NetworkUser::where('tenant_id', $tenantId)
                ->where('status', '!=', 'suspended')
                ->where('is_active', true)
                ->whereDate('expiry_date', $today)
                ->with(['package', 'user'])
                ->get();

            $totalCount = $usersAtRisk->count();
            $totalAmount = $usersAtRisk->sum(function ($user) {
                return $user->package->price ?? 0;
            });

            // Count by package
            $byPackage = $usersAtRisk->groupBy('package_id')->map(function ($group, $packageId) {
                $package = $group->first()->package;

                return [
                    'package_name' => $package->name ?? 'Unknown',
                    'count' => $group->count(),
                    'amount' => $group->sum(fn($u) => $u->package->price ?? 0),
                ];
            })->values()->toArray();

            // Count by zone (if zone_id exists on users)
            $byZone = $usersAtRisk->groupBy(function ($user) {
                return $user->user->zone_id ?? 'unassigned';
            })->map(function ($group, $zoneId) {
                return [
                    'zone_id' => $zoneId,
                    'count' => $group->count(),
                    'amount' => $group->sum(fn($u) => $u->package->price ?? 0),
                ];
            })->values()->toArray();

            return [
                'total_count' => $totalCount,
                'total_amount' => round($totalAmount, 2),
                'by_package' => $byPackage,
                'by_zone' => $byZone,
                'date' => $today->toDateString(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate suspension forecast', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_count' => 0,
                'total_amount' => 0,
                'by_package' => [],
                'by_zone' => [],
                'date' => Carbon::today()->toDateString(),
            ];
        }
    }

    /**
     * Calculate collection target data.
     */
    private function calculateCollectionTarget(int $tenantId): array
    {
        try {
            $today = Carbon::today();

            // Get bills due today
            $billsDueToday = SubscriptionBill::where('tenant_id', $tenantId)
                ->whereDate('due_date', $today)
                ->get();

            $targetAmount = $billsDueToday->sum('total_amount');

            // Get collected amount (paid bills due today)
            $collectedAmount = $billsDueToday
                ->where('status', SubscriptionBill::STATUS_PAID)
                ->sum('total_amount');

            $pendingAmount = $targetAmount - $collectedAmount;
            $percentageCollected = $targetAmount > 0
                ? round(($collectedAmount / $targetAmount) * 100, 2)
                : 0;

            return [
                'target_amount' => round($targetAmount, 2),
                'collected_amount' => round($collectedAmount, 2),
                'pending_amount' => round($pendingAmount, 2),
                'percentage_collected' => $percentageCollected,
                'total_bills' => $billsDueToday->count(),
                'paid_bills' => $billsDueToday->where('status', SubscriptionBill::STATUS_PAID)->count(),
                'pending_bills' => $billsDueToday->where('status', SubscriptionBill::STATUS_PENDING)->count(),
                'date' => $today->toDateString(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate collection target', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'target_amount' => 0,
                'collected_amount' => 0,
                'pending_amount' => 0,
                'percentage_collected' => 0,
                'total_bills' => 0,
                'paid_bills' => 0,
                'pending_bills' => 0,
                'date' => Carbon::today()->toDateString(),
            ];
        }
    }

    /**
     * Calculate SMS usage data.
     */
    private function calculateSmsUsage(int $tenantId): array
    {
        try {
            $today = Carbon::today();

            // Get SMS sent today
            $smsSentToday = SmsLog::where('tenant_id', $tenantId)
                ->whereDate('created_at', $today)
                ->get();

            $totalSent = $smsSentToday->count();
            $totalCost = $smsSentToday->sum('cost');

            // Count by status
            $sentCount = $smsSentToday->where('status', SmsLog::STATUS_SENT)->count()
                + $smsSentToday->where('status', SmsLog::STATUS_DELIVERED)->count();
            $failedCount = $smsSentToday->where('status', SmsLog::STATUS_FAILED)->count();
            $pendingCount = $smsSentToday->where('status', SmsLog::STATUS_PENDING)->count();

            // Calculate remaining balance (simplified - would need SMS balance tracking)
            // TODO: Implement actual SMS balance tracking system
            // For now, return null to indicate balance tracking is not available
            $remainingBalance = null; // Will be implemented with SMS balance tracking feature
            $usedBalance = $totalCost;

            return [
                'total_sent' => $totalSent,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'pending_count' => $pendingCount,
                'total_cost' => round($totalCost, 4),
                'remaining_balance' => $remainingBalance, // null until balance tracking is implemented
                'used_balance' => round($usedBalance, 4),
                'balance_tracking_available' => false, // Flag to indicate feature not yet implemented
                'date' => $today->toDateString(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate SMS usage', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_sent' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'pending_count' => 0,
                'total_cost' => 0,
                'remaining_balance' => null,
                'used_balance' => 0,
                'balance_tracking_available' => false,
                'date' => Carbon::today()->toDateString(),
            ];
        }
    }
}
