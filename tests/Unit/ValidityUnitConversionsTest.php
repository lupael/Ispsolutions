<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Package;
use App\Models\MasterPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Validity unit conversions
 * Tests validity conversion methods for days, hours, and minutes
 */
class ValidityUnitConversionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_validity_in_days_converts_correctly(): void
    {
        $package = Package::factory()->create([
            'validity_days' => 30,
        ]);

        $this->assertEquals(30, $package->validity_in_days);
    }

    public function test_validity_in_hours_converts_days_correctly(): void
    {
        $package = Package::factory()->create([
            'validity_days' => 30,
        ]);

        $this->assertEquals(720, $package->validity_in_hours); // 30 days * 24 hours
    }

    public function test_validity_in_minutes_converts_days_correctly(): void
    {
        $package = Package::factory()->create([
            'validity_days' => 1,
        ]);

        $this->assertEquals(1440, $package->validity_in_minutes); // 1 day * 24 hours * 60 minutes
    }

    public function test_validity_conversions_handle_zero(): void
    {
        $package = Package::factory()->create([
            'validity_days' => 0,
        ]);

        $this->assertEquals(0, $package->validity_in_days);
        $this->assertEquals(0, $package->validity_in_hours);
        $this->assertEquals(0, $package->validity_in_minutes);
    }

    public function test_master_package_validity_conversions(): void
    {
        $masterPackage = MasterPackage::factory()->create([
            'validity' => 7,
            'validity_unit' => 'Day',
        ]);

        $this->assertEquals(7, $masterPackage->validity_in_days);
        $this->assertEquals(168, $masterPackage->validity_in_hours); // 7 * 24
        $this->assertEquals(10080, $masterPackage->validity_in_minutes); // 7 * 24 * 60
    }

    public function test_master_package_hour_unit_conversions(): void
    {
        $masterPackage = MasterPackage::factory()->create([
            'validity' => 24,
            'validity_unit' => 'Hour',
        ]);

        $this->assertEquals(1, $masterPackage->validity_in_days); // 24 hours = 1 day
        $this->assertEquals(24, $masterPackage->validity_in_hours);
        $this->assertEquals(1440, $masterPackage->validity_in_minutes); // 24 * 60
    }

    public function test_master_package_minute_unit_conversions(): void
    {
        $masterPackage = MasterPackage::factory()->create([
            'validity' => 60,
            'validity_unit' => 'Minute',
        ]);

        $this->assertEquals(0, $masterPackage->validity_in_days); // 60 minutes = 0 days (rounds down)
        $this->assertEquals(1, $masterPackage->validity_in_hours); // 60 minutes = 1 hour
        $this->assertEquals(60, $masterPackage->validity_in_minutes);
    }
}
