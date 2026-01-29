<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\BillingProfileCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test Billing Profile Cache Service
 * 
 * Tests focus on cache behavior - cache hits, invalidation, and TTL.
 * Note: These are unit tests for the caching mechanism, not integration tests
 * for the full query logic (which would require database fixtures).
 * 
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #4
 */
class BillingProfileCacheServiceTest extends TestCase
{
    private BillingProfileCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BillingProfileCacheService();
        Cache::flush();
    }

    /**
     * Test that cache key is correctly formed and used
     */
    public function test_get_billing_profiles_cache_key(): void
    {
        $tenantId = 1;
        $cacheKey = "billing_profiles:tenant:{$tenantId}";

        // Manually set cache to avoid database call
        Cache::put($cacheKey, collect(['profile1', 'profile2']), 300);
        
        // Verify cache was set
        $this->assertTrue(Cache::has($cacheKey));
        
        // Get profiles should use cached data
        $profiles = $this->service->getBillingProfiles($tenantId);
        
        $this->assertIsObject($profiles);
        $this->assertCount(2, $profiles);
    }

    /**
     * Test refresh flag functionality by validating cache clearing
     */
    public function test_refresh_flag_clears_cache(): void
    {
        $tenantId = 1;
        $cacheKey = "billing_profiles:tenant:{$tenantId}";

        // Populate cache with test data
        Cache::put($cacheKey, collect(['old_data']), 300);
        
        // Verify cache exists
        $this->assertTrue(Cache::has($cacheKey));
        
        // Invalidate cache to simulate refresh
        $this->service->invalidateTenantCache($tenantId);
        
        // Verify cache was cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /**
     * Test cache invalidation for single profile
     */
    public function test_invalidate_billing_profile_cache(): void
    {
        $profileId = 1;

        // Populate cache
        Cache::put("billing_profile:{$profileId}", ['test' => 'data'], 300);
        Cache::put("billing_profile_customer_count:{$profileId}", 10, 300);
        
        // Verify cache exists
        $this->assertTrue(Cache::has("billing_profile:{$profileId}"));
        $this->assertTrue(Cache::has("billing_profile_customer_count:{$profileId}"));
        
        // Invalidate cache
        $this->service->invalidateCache($profileId);
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has("billing_profile:{$profileId}"));
        $this->assertFalse(Cache::has("billing_profile_customer_count:{$profileId}"));
    }

    /**
     * Test cache invalidation for entire tenant
     */
    public function test_invalidate_tenant_cache(): void
    {
        $tenantId = 1;

        // Populate cache
        Cache::put("billing_profiles:tenant:{$tenantId}", ['test' => 'data'], 300);
        
        // Verify cache exists
        $this->assertTrue(Cache::has("billing_profiles:tenant:{$tenantId}"));
        
        // Invalidate cache
        $this->service->invalidateTenantCache($tenantId);
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has("billing_profiles:tenant:{$tenantId}"));
    }

    /**
     * Test customer count cache key and invalidation
     */
    public function test_customer_count_cache_invalidation(): void
    {
        $profileId = 1;
        $cacheKey = "billing_profile_customer_count:{$profileId}";

        // Manually set cache
        Cache::put($cacheKey, 42, 300);
        
        // Verify cache was set
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals(42, Cache::get($cacheKey));
        
        // Invalidate should clear it
        $this->service->invalidateCache($profileId);
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has($cacheKey));
    }
}
