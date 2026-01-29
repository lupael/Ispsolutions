<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\SmsPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SMS Payment Feature Tests
 * 
 * Tests the SMS payment endpoints and workflows
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class SmsPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /**
     * Test operator can view their SMS payments
     */
    public function test_operator_can_view_sms_payments(): void
    {
        $operator = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_OPERATOR]);
        $operator->assignRole('operator');

        // Create some payments
        SmsPayment::factory()->count(5)->create(['operator_id' => $operator->id]);

        $this->actingAs($operator);

        $response = $this->getJson('/api/sms-payments');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => ['id', 'operator_id', 'amount', 'sms_quantity', 'status'],
                ],
            ],
        ]);
    }

    /**
     * Test operator can create SMS payment
     */
    public function test_operator_can_create_sms_payment(): void
    {
        $operator = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_OPERATOR]);
        $operator->assignRole('operator');

        $this->actingAs($operator);

        $response = $this->postJson('/api/sms-payments', [
            'sms_quantity' => 1000,
            'payment_method' => 'bkash',
            'amount' => 500.00,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'SMS payment initiated successfully',
        ]);

        $this->assertDatabaseHas('sms_payments', [
            'operator_id' => $operator->id,
            'sms_quantity' => 1000,
            'amount' => 500.00,
            'payment_method' => 'bkash',
            'status' => 'pending',
        ]);
    }

    /**
     * Test SMS payment validation fails with invalid data
     */
    public function test_sms_payment_validation_fails(): void
    {
        $operator = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_OPERATOR]);
        $operator->assignRole('operator');

        $this->actingAs($operator);

        $response = $this->postJson('/api/sms-payments', [
            'sms_quantity' => 50, // Less than minimum 100
            'payment_method' => 'invalid_method',
            // amount is not included in request as it's calculated server-side
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['sms_quantity', 'payment_method']);
    }

    /**
     * Test customer cannot create SMS payment
     */
    public function test_customer_cannot_create_sms_payment(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);
        $customer->assignRole('customer');

        $this->actingAs($customer);

        $response = $this->postJson('/api/sms-payments', [
            'sms_quantity' => 1000,
            'payment_method' => 'bkash',
            'amount' => 500.00,
        ]);

        $response->assertStatus(403);
    }
}
