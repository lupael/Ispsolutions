<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BillingProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Billing Profile Cache Service
 * 
 * Provides caching for billing profile data
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #4
 */
class BillingProfileCacheService
{
    private const CACHE_TTL = 300; // 5 minutes for billing profile caching

    /**
     * Get cached billing profiles for a tenant
     * 
     * @param int $tenantId The tenant ID
     * @param bool $refresh Whether to refresh the cache
     * @return Collection<BillingProfile>
     */
    public function getBillingProfiles(int $tenantId, bool $refresh = false): Collection
    {
        $cacheKey = "billing_profiles:tenant:{$tenantId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            return BillingProfile::where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get a cached billing profile by ID
     * 
     * @param int $profileId The billing profile ID
     * @param bool $refresh Whether to refresh the cache
     * @return BillingProfile|null
     */
    public function getBillingProfile(int $profileId, bool $refresh = false): ?BillingProfile
    {
        $cacheKey = "billing_profile:{$profileId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($profileId) {
            return BillingProfile::find($profileId);
        });
    }

    /**
     * Get cached customer count for a billing profile
     * 
     * @param int $profileId The billing profile ID
     * @param bool $refresh Whether to refresh the cache
     * @return int
     */
    public function getCustomerCount(int $profileId, bool $refresh = false): int
    {
        $cacheKey = "billing_profile_customer_count:{$profileId}";

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($profileId) {
            return User::where('billing_profile_id', $profileId)
                ->where('operator_level', 100) // Customers only
                ->count();
        });
    }

    /**
     * Invalidate cache for a billing profile
     * 
     * @param int $profileId The billing profile ID
     * @return void
     */
    public function invalidateCache(int $profileId): void
    {
        Cache::forget("billing_profile:{$profileId}");
        Cache::forget("billing_profile_customer_count:{$profileId}");
    }

    /**
     * Invalidate cache for all billing profiles of a tenant
     * 
     * @param int $tenantId The tenant ID
     * @return void
     */
    public function invalidateTenantCache(int $tenantId): void
    {
        Cache::forget("billing_profiles:tenant:{$tenantId}");
    }
}
