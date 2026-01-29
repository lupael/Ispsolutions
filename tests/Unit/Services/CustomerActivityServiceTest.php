<?php

namespace Tests\Unit\Services;

use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CustomerActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for CustomerActivityService
 * 
 * These tests validate that the service correctly handles payment amounts
 * that are returned as strings from Laravel's decimal cast.
 */
class CustomerActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerActivityService $service;
    protected Tenant $tenant;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new CustomerActivityService();
        $this->tenant = Tenant::factory()->create();
        $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /** @test */
    public function it_formats_payment_amounts_correctly_in_activity_timeline()
    {
        // Create a payment with amount that will be cast to string by decimal cast
        Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => null,
            'amount' => '100.50', // This becomes a string due to decimal:2 cast
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // This should not throw a TypeError
        $activities = $this->service->getRecentActivities($this->customer);

        $this->assertCount(1, $activities);
        $activity = $activities->first();
        
        $this->assertEquals('payment', $activity['type']);
        // Verify the amount is properly formatted in the description
        $this->assertStringContainsString('100.50', $activity['description']);
        $this->assertStringContainsString('cash', $activity['description']);
    }

    /** @test */
    public function it_handles_large_decimal_amounts_with_comma_formatting()
    {
        // Create payments with large amounts
        Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => null,
            'amount' => '1234.56',
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
        ]);

        Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => null,
            'amount' => '9999.99',
            'payment_method' => 'card',
            'status' => 'completed',
        ]);

        $activities = $this->service->getActivityTimeline($this->customer, 50);

        $this->assertCount(2, $activities);
        
        // Check that amounts are formatted with commas
        $descriptions = $activities->pluck('description')->toArray();
        $this->assertStringContainsString('1,234.56', $descriptions[0]);
        $this->assertStringContainsString('9,999.99', $descriptions[1]);
    }

    /** @test */
    public function it_handles_zero_and_integer_amounts()
    {
        Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => null,
            'amount' => '0',
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => null,
            'amount' => '100',
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $activities = $this->service->getActivityTimeline($this->customer, 50);

        $this->assertCount(2, $activities);
        
        // Verify amounts are formatted with two decimal places
        $descriptions = $activities->pluck('description')->toArray();
        $this->assertStringContainsString('0.00', $descriptions[0]);
        $this->assertStringContainsString('100.00', $descriptions[1]);
    }

    /** @test */
    public function it_returns_empty_collection_when_customer_has_no_payments()
    {
        $activities = $this->service->getRecentActivities($this->customer);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $activities);
        $this->assertCount(0, $activities);
    }
}
