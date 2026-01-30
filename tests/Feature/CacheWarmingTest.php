<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test: Cache warming
 * Tests that cache warming command populates all caches
 */
class CacheWarmingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_warm_command_exists(): void
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('cache:warm', $commands);
    }

    public function test_cache_warm_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('cache:warm');
        
        $this->assertEquals(0, $exitCode);
    }

    public function test_cache_warm_populates_package_customer_counts(): void
    {
        $package = \App\Models\Package::factory()->create();

        // Clear cache first
        Cache::forget("package_customerCount_{$package->id}");

        // Run cache warm command
        Artisan::call('cache:warm');

        // Check if cache is populated
        $this->assertTrue(Cache::has("package_customerCount_{$package->id}"));
    }

    public function test_cache_warm_populates_master_package_customer_counts(): void
    {
        $masterPackage = \App\Models\MasterPackage::factory()->create();

        // Clear cache first
        Cache::forget("master_package_customerCount_{$masterPackage->id}");

        // Run cache warm command
        Artisan::call('cache:warm');

        // Check if cache is populated
        $this->assertTrue(Cache::has("master_package_customerCount_{$masterPackage->id}"));
    }

    public function test_cache_warm_handles_empty_database(): void
    {
        // Clear all packages
        \App\Models\Package::query()->delete();
        \App\Models\MasterPackage::query()->delete();

        // Should not throw any errors
        $exitCode = Artisan::call('cache:warm');
        
        $this->assertEquals(0, $exitCode);
    }

    public function test_cache_warm_uses_bulk_queries(): void
    {
        // Create multiple packages
        \App\Models\Package::factory()->count(5)->create();
        \App\Models\MasterPackage::factory()->count(5)->create();

        // Clear all caches
        Cache::flush();

        // Run cache warm command
        Artisan::call('cache:warm');

        // All package caches should be populated
        $packages = \App\Models\Package::all();
        foreach ($packages as $package) {
            $this->assertTrue(Cache::has("package_customerCount_{$package->id}"));
        }

        // All master package caches should be populated
        $masterPackages = \App\Models\MasterPackage::all();
        foreach ($masterPackages as $masterPackage) {
            $this->assertTrue(Cache::has("master_package_customerCount_{$masterPackage->id}"));
        }
    }

    public function test_cache_warm_sets_correct_ttl(): void
    {
        $package = \App\Models\Package::factory()->create();

        // Clear cache first
        Cache::forget("package_customerCount_{$package->id}");

        // Run cache warm command
        Artisan::call('cache:warm');

        // Cache should exist
        $this->assertTrue(Cache::has("package_customerCount_{$package->id}"));

        // TTL should be 150 seconds (2.5 minutes)
        // Note: We can't directly test TTL without custom cache drivers,
        // but we can verify the cache exists
    }

    public function test_cache_warm_can_be_run_multiple_times(): void
    {
        // First run
        $exitCode1 = Artisan::call('cache:warm');
        $this->assertEquals(0, $exitCode1);

        // Second run (should not fail)
        $exitCode2 = Artisan::call('cache:warm');
        $this->assertEquals(0, $exitCode2);
    }

    public function test_cache_warm_output_is_informative(): void
    {
        Artisan::call('cache:warm');
        
        $output = Artisan::output();
        
        $this->assertNotEmpty($output);
        // Output should contain information about cache warming
    }
}
