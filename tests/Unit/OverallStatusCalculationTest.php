<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\CustomerOverallStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Overall status calculation
 * Tests that overall_status combines payment and service status correctly
 */
class OverallStatusCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepaid_active_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_ACTIVE, $customer->overall_status);
    }

    public function test_prepaid_suspended_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'suspended',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_SUSPENDED, $customer->overall_status);
    }

    public function test_prepaid_expired_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'expired',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_EXPIRED, $customer->overall_status);
    }

    public function test_prepaid_inactive_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'inactive',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_INACTIVE, $customer->overall_status);
    }

    public function test_postpaid_active_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'active',
        ]);

        $this->assertEquals(CustomerOverallStatus::POSTPAID_ACTIVE, $customer->overall_status);
    }

    public function test_postpaid_suspended_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'suspended',
        ]);

        $this->assertEquals(CustomerOverallStatus::POSTPAID_SUSPENDED, $customer->overall_status);
    }

    public function test_postpaid_expired_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'expired',
        ]);

        $this->assertEquals(CustomerOverallStatus::POSTPAID_EXPIRED, $customer->overall_status);
    }

    public function test_postpaid_inactive_status(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'inactive',
        ]);

        $this->assertEquals(CustomerOverallStatus::POSTPAID_INACTIVE, $customer->overall_status);
    }

    public function test_overall_status_updates_when_payment_type_changes(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_ACTIVE, $customer->overall_status);

        $customer->update(['payment_type' => 'postpaid']);
        $customer->refresh();

        $this->assertEquals(CustomerOverallStatus::POSTPAID_ACTIVE, $customer->overall_status);
    }

    public function test_overall_status_updates_when_status_changes(): void
    {
        $customer = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        $this->assertEquals(CustomerOverallStatus::PREPAID_ACTIVE, $customer->overall_status);

        $customer->update(['status' => 'suspended']);
        $customer->refresh();

        $this->assertEquals(CustomerOverallStatus::PREPAID_SUSPENDED, $customer->overall_status);
    }
}
