<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OperatorStatsCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test Operator Statistics Cache Service
 * 
 * Tests focus on cache behavior - cache hits, invalidation, and TTL.
 * Note: These are unit tests for the caching mechanism, not integration tests
 * for the full aggregation logic (which would require database fixtures).
 * 
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #4
 */
class OperatorStatsCacheServiceTest extends TestCase
{
    private OperatorStatsCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OperatorStatsCacheService();
        Cache::flush();
    }

    /**
     * Test that dashboard stats use cache
     */
    public function test_get_dashboard_stats_uses_cache(): void
    {
        $operatorId = 1;
        $cacheKey = "operator_stats:dashboard:{$operatorId}";

        // First call populates cache
        $stats = $this->service->getDashboardStats($operatorId);
        
        // Verify cache was populated
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call uses cache
        $cachedStats = $this->service->getDashboardStats($operatorId);
        
        $this->assertIsArray($stats);
        $this->assertEquals($stats, $cachedStats);
        $this->assertArrayHasKey('total_customers', $stats);
        $this->assertArrayHasKey('active_customers', $stats);
        $this->assertArrayHasKey('timestamp', $stats);
    }

    /**
     * Test that refresh flag bypasses cache
     */
    public function test_refresh_dashboard_stats_bypasses_cache(): void
    {
        $operatorId = 1;
        $cacheKey = "operator_stats:dashboard:{$operatorId}";

        // Populate cache with mock data
        Cache::put($cacheKey, ['mock' => 'data'], 300);
        
        // Verify cache exists
        $this->assertTrue(Cache::has($cacheKey));
        
        // Refresh should clear and repopulate
        $stats = $this->service->getDashboardStats($operatorId, true);
        
        $this->assertIsArray($stats);
        // Cache should still exist after refresh
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test customer stats caching behavior
     */
    public function test_get_customer_stats_uses_cache(): void
    {
        $operatorId = 1;
        $cacheKey = "operator_stats:customers:{$operatorId}";

        // First call populates cache
        $stats = $this->service->getCustomerStats($operatorId);
        
        // Verify cache was populated
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call uses cache
        $cachedStats = $this->service->getCustomerStats($operatorId);
        
        $this->assertIsArray($stats);
        $this->assertEquals($stats, $cachedStats);
    }

    /**
     * Test revenue stats caching behavior
     */
    public function test_get_revenue_stats_uses_cache(): void
    {
        $operatorId = 1;
        $cacheKey = "operator_stats:revenue:{$operatorId}";

        // First call populates cache
        $stats = $this->service->getRevenueStats($operatorId);
        
        // Verify cache was populated
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call uses cache
        $cachedStats = $this->service->getRevenueStats($operatorId);
        
        $this->assertIsArray($stats);
        $this->assertEquals($stats, $cachedStats);
    }

    /**
     * Test cache invalidation clears all operator caches
     */
    public function test_invalidate_operator_cache(): void
    {
        $operatorId = 1;

        // Populate cache
        Cache::put("operator_stats:dashboard:{$operatorId}", ['test' => 'data'], 300);
        Cache::put("operator_stats:customers:{$operatorId}", ['test' => 'data'], 300);
        Cache::put("operator_stats:revenue:{$operatorId}", ['test' => 'data'], 60);
        
        // Verify cache exists
        $this->assertTrue(Cache::has("operator_stats:dashboard:{$operatorId}"));
        $this->assertTrue(Cache::has("operator_stats:customers:{$operatorId}"));
        $this->assertTrue(Cache::has("operator_stats:revenue:{$operatorId}"));
        
        // Invalidate cache
        $this->service->invalidateCache($operatorId);
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has("operator_stats:dashboard:{$operatorId}"));
        $this->assertFalse(Cache::has("operator_stats:customers:{$operatorId}"));
        $this->assertFalse(Cache::has("operator_stats:revenue:{$operatorId}"));
    }

    /**
     * Test dashboard stats return expected structure
     */
    public function test_dashboard_stats_structure(): void
    {
        $operatorId = 1;

        $stats = $this->service->getDashboardStats($operatorId);
        
        $this->assertArrayHasKey('total_customers', $stats);
        $this->assertArrayHasKey('active_customers', $stats);
        $this->assertArrayHasKey('suspended_customers', $stats);
        $this->assertArrayHasKey('expired_customers', $stats);
        $this->assertArrayHasKey('timestamp', $stats);
        
        $this->assertIsInt($stats['total_customers']);
        $this->assertIsInt($stats['active_customers']);
        $this->assertIsInt($stats['suspended_customers']);
        $this->assertIsInt($stats['expired_customers']);
    }
}
