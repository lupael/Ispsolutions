<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiLevelCommissionTest extends TestCase
{
    use RefreshDatabase;

    private $commissionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->commissionService = $this->app->make(CommissionService::class);
    }

    /** @test */
    public function it_calculates_multi_level_commissions_correctly()
    {
        // 1. Create an operator, a sub-operator, and a customer
        $operator = User::factory()->create(['operator_level' => 30]);
        $operator->assignRole('operator');

        $subOperator = User::factory()->create(['operator_level' => 40, 'parent_id' => $operator->id]);
        $subOperator->assignRole('sub-operator');

        $customer = User::factory()->create(['is_subscriber' => true, 'parent_id' => $subOperator->id]);

        // 2. Create a payment for the customer
        $payment = Payment::factory()->create(['user_id' => $customer->id, 'amount' => 100]);

        // 3. Call the calculateMultiLevelCommission method
        $commissions = $this->commissionService->calculateMultiLevelCommission($payment);

        // 4. Assert that two commissions are created
        $this->assertCount(2, $commissions);

        // 5. Assert that the commission amounts are calculated correctly
        $subOperatorCommission = $commissions[0];
        $operatorCommission = $commissions[1];

        $this->assertEquals($subOperator->id, $subOperatorCommission->reseller_id);
        $this->assertEquals(5.0, $subOperatorCommission->commission_amount);

        $this->assertEquals($operator->id, $operatorCommission->reseller_id);
        $this->assertEquals(3.0, $operatorCommission->commission_amount);
    }
}
