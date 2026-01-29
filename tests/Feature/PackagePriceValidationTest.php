<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test package price validation
 * 
 * Ensures that packages cannot be created with prices below $1
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #1 (Package Price Validation)
 */
class PackagePriceValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that package creation with zero monthly price is rejected
     */
    public function test_package_creation_rejects_zero_monthly_price(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 0,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price_monthly']);
        $response->assertJson([
            'errors' => [
                'price_monthly' => ['Monthly price must be at least $1.'],
            ],
        ]);
    }

    /**
     * Test that package creation with negative monthly price is rejected
     */
    public function test_package_creation_rejects_negative_monthly_price(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => -5,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price_monthly']);
    }

    /**
     * Test that package creation with zero daily price is rejected
     */
    public function test_package_creation_rejects_zero_daily_price(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 100,
            'price_daily' => 0,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price_daily']);
        $response->assertJson([
            'errors' => [
                'price_daily' => ['Daily price must be at least $1.'],
            ],
        ]);
    }

    /**
     * Test that package creation with valid minimum price succeeds
     */
    public function test_package_creation_accepts_minimum_valid_price(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 1,
            'price_daily' => 1,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $response->assertStatus(201);
    }

    /**
     * Test that package update with zero price is rejected
     */
    public function test_package_update_rejects_zero_price(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        // First create a valid package
        $createResponse = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 50,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $createResponse->assertStatus(201);
        $packageId = $createResponse->json('data.id');

        // Try to update with zero price
        $updateResponse = $this->putJson("/api/packages/{$packageId}", [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 0,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $updateResponse->assertStatus(422);
        $updateResponse->assertJsonValidationErrors(['price_monthly']);
    }

    /**
     * Test that package creation with valid price above minimum succeeds
     */
    public function test_package_creation_accepts_price_above_minimum(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/packages', [
            'name' => 'Test Package',
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'bandwidth_unit' => 'mbps',
            'price_monthly' => 99.99,
            'price_daily' => 5.50,
            'validity_days' => 30,
            'validity_unit' => 'days',
            'connection_type' => 'pppoe',
        ]);

        $response->assertStatus(201);
    }
}
