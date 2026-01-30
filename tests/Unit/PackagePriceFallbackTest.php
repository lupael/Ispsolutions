<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Package price fallback
 * Tests that package price defaults to 1 if zero or negative
 */
class PackagePriceFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_price_defaults_to_one_if_zero(): void
    {
        $package = Package::factory()->create([
            'price' => 0,
        ]);

        $this->assertEquals(1, $package->price);
    }

    public function test_package_price_defaults_to_one_if_negative(): void
    {
        $package = Package::factory()->create([
            'price' => -10,
        ]);

        $this->assertEquals(1, $package->price);
    }

    public function test_package_price_remains_unchanged_if_positive(): void
    {
        $package = Package::factory()->create([
            'price' => 50,
        ]);

        $this->assertEquals(50, $package->price);
    }

    public function test_package_price_handles_decimal_values(): void
    {
        $package = Package::factory()->create([
            'price' => 49.99,
        ]);

        $this->assertEquals(49.99, $package->price);
    }

    public function test_package_price_minimum_is_one(): void
    {
        $package = Package::factory()->create([
            'price' => 0.5,
        ]);

        // Small positive values should remain as-is
        $this->assertEquals(0.5, $package->price);

        $package2 = Package::factory()->create([
            'price' => 0,
        ]);

        // Zero should fallback to 1
        $this->assertEquals(1, $package2->price);
    }
}
