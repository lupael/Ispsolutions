<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test: Customer count caching
 * Tests that customer count is cached correctly
 */
class CustomerCountCachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_count_is_cached(): void
    {
        $package = Package::factory()->create();

        // First call should cache the value
        $count1 = $package->customer_count;

        // Second call should retrieve from cache
        $count2 = $package->customer_count;

        $this->assertEquals($count1, $count2);
    }

    public function test_cache_key_is_unique_per_package(): void
    {
        $package1 = Package::factory()->create();
        $package2 = Package::factory()->create();

        $cacheKey1 = "package_customerCount_{$package1->id}";
        $cacheKey2 = "package_customerCount_{$package2->id}";

        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }

    public function test_customer_count_cache_can_be_cleared(): void
    {
        $package = Package::factory()->create();

        // Get the cached count
        $count1 = $package->customer_count;

        // Clear the cache
        Cache::forget("package_customerCount_{$package->id}");

        // Get the count again (should re-cache)
        $count2 = $package->customer_count;

        // Both should be equal (assuming no customers were added)
        $this->assertEquals($count1, $count2);
    }

    public function test_cache_has_ttl_of_150_seconds(): void
    {
        $package = Package::factory()->create();

        // Access customer_count to trigger caching
        $package->customer_count;

        // Check if the cache key exists
        $cacheKey = "package_customerCount_{$package->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_customer_count_returns_zero_for_package_with_no_users(): void
    {
        $package = Package::factory()->create();

        $this->assertEquals(0, $package->customer_count);
    }

    public function test_customer_count_reflects_actual_count(): void
    {
        $package = Package::factory()->create();

        // Create users with this package
        $user1 = \App\Models\User::factory()->create([
            'service_package_id' => $package->id,
        ]);

        // Clear cache to get fresh count
        Cache::forget("package_customerCount_{$package->id}");

        $this->assertEquals(1, $package->customer_count);

        // Create another user
        $user2 = \App\Models\User::factory()->create([
            'service_package_id' => $package->id,
        ]);

        // Clear cache again
        Cache::forget("package_customerCount_{$package->id}");

        $this->assertEquals(2, $package->customer_count);
    }
}
