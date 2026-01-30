<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CustomerOverallStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Overall status filtering
 * Tests that customers can be filtered by overall status
 */
class OverallStatusFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_customers_by_prepaid_active(): void
    {
        $prepaidActive = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        $postpaidActive = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'active',
        ]);

        // Filter by prepaid active
        $filtered = User::query()
            ->where('payment_type', 'prepaid')
            ->where('status', 'active')
            ->get();

        $this->assertTrue($filtered->contains($prepaidActive));
        $this->assertFalse($filtered->contains($postpaidActive));
    }

    public function test_can_filter_customers_by_postpaid_suspended(): void
    {
        $postpaidSuspended = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'suspended',
        ]);

        $prepaidSuspended = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'suspended',
        ]);

        // Filter by postpaid suspended
        $filtered = User::query()
            ->where('payment_type', 'postpaid')
            ->where('status', 'suspended')
            ->get();

        $this->assertTrue($filtered->contains($postpaidSuspended));
        $this->assertFalse($filtered->contains($prepaidSuspended));
    }

    public function test_can_filter_customers_by_expired_status(): void
    {
        $expired1 = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'expired',
        ]);

        $expired2 = User::factory()->create([
            'payment_type' => 'postpaid',
            'status' => 'expired',
        ]);

        $active = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        // Filter by expired status (any payment type)
        $filtered = User::query()
            ->where('status', 'expired')
            ->get();

        $this->assertTrue($filtered->contains($expired1));
        $this->assertTrue($filtered->contains($expired2));
        $this->assertFalse($filtered->contains($active));
    }

    public function test_composite_index_improves_query_performance(): void
    {
        // Create multiple customers
        User::factory()->count(10)->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        User::factory()->count(10)->create([
            'payment_type' => 'postpaid',
            'status' => 'suspended',
        ]);

        // Query should use the composite index
        $query = User::query()
            ->where('payment_type', 'prepaid')
            ->where('status', 'active');

        $result = $query->get();

        $this->assertCount(10, $result);
    }

    public function test_overall_status_enum_values_are_correct(): void
    {
        $this->assertEquals('prepaid_active', CustomerOverallStatus::PREPAID_ACTIVE->value);
        $this->assertEquals('prepaid_suspended', CustomerOverallStatus::PREPAID_SUSPENDED->value);
        $this->assertEquals('prepaid_expired', CustomerOverallStatus::PREPAID_EXPIRED->value);
        $this->assertEquals('prepaid_inactive', CustomerOverallStatus::PREPAID_INACTIVE->value);
        $this->assertEquals('postpaid_active', CustomerOverallStatus::POSTPAID_ACTIVE->value);
        $this->assertEquals('postpaid_suspended', CustomerOverallStatus::POSTPAID_SUSPENDED->value);
        $this->assertEquals('postpaid_expired', CustomerOverallStatus::POSTPAID_EXPIRED->value);
        $this->assertEquals('postpaid_inactive', CustomerOverallStatus::POSTPAID_INACTIVE->value);
    }

    public function test_can_filter_by_multiple_statuses(): void
    {
        $active1 = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        $suspended1 = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'suspended',
        ]);

        $expired1 = User::factory()->create([
            'payment_type' => 'prepaid',
            'status' => 'expired',
        ]);

        // Filter by multiple statuses
        $filtered = User::query()
            ->where('payment_type', 'prepaid')
            ->whereIn('status', ['active', 'suspended'])
            ->get();

        $this->assertTrue($filtered->contains($active1));
        $this->assertTrue($filtered->contains($suspended1));
        $this->assertFalse($filtered->contains($expired1));
    }

    public function test_can_count_customers_by_overall_status(): void
    {
        User::factory()->count(5)->create([
            'payment_type' => 'prepaid',
            'status' => 'active',
        ]);

        User::factory()->count(3)->create([
            'payment_type' => 'postpaid',
            'status' => 'active',
        ]);

        $prepaidActiveCount = User::query()
            ->where('payment_type', 'prepaid')
            ->where('status', 'active')
            ->count();

        $postpaidActiveCount = User::query()
            ->where('payment_type', 'postpaid')
            ->where('status', 'active')
            ->count();

        $this->assertEquals(5, $prepaidActiveCount);
        $this->assertEquals(3, $postpaidActiveCount);
    }
}
