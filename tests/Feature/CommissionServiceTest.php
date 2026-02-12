<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $reseller;

    protected User $customer;

    protected CommissionService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();

        $adminRole = Role::factory()->create(['name' => 'admin', 'level' => 90, 'slug' => 'admin']);
        $operatorRole = Role::factory()->create(['name' => 'operator', 'level' => 60, 'slug' => 'operator']);
        $subOperatorRole = Role::factory()->create(['name' => 'sub-operator', 'level' => 40, 'slug' => 'sub-operator']);
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10, 'slug' => 'customer']);

        $this->admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->admin->roles()->attach($adminRole);

        $this->operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $this->admin->id,
        ]);
        $this->operator->roles()->attach($operatorRole);

        $this->subOperator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $this->operator->id,
        ]);
        $this->subOperator->roles()->attach($subOperatorRole);

        $this->customer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $this->subOperator->id,
        ]);
        $this->customer->roles()->attach($customerRole);

        $this->commissionService = app(CommissionService::class);
    }

    public function test_can_calculate_commission_for_operator(): void
    {
        config(['commission.rates.operator.direct' => 10.0]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->customer->tenant_id,
            'user_id' => $this->customer->id,
            'amount' => 1000.00,
            'status' => 'completed',
        ]);

        $commissions = $this->commissionService->calculateMultiLevelCommission($payment);
        $commission = $commissions[1];

        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals($this->subOperator->id, $commission->reseller_id);
        $this->assertEquals($payment->id, $commission->payment_id);
        $this->assertEquals(100.00, $commission->commission_amount);
        $this->assertEquals(10.0, $commission->commission_percentage);
        $this->assertEquals('pending', $commission->status);
    }

    public function test_can_get_operator_commission_summary(): void
    {
        Commission::factory()->count(3)->create([
            'tenant_id' => $this->operator->tenant_id,
            'reseller_id' => $this->operator->id,
            'commission_amount' => 100.00,
            'status' => 'pending',
        ]);

        Commission::factory()->count(2)->create([
            'tenant_id' => $this->operator->tenant_id,
            'reseller_id' => $this->operator->id,
            'commission_amount' => 50.00,
            'status' => 'paid',
        ]);

        $summary = $this->commissionService->getOperatorCommissionSummary($this->operator);

        $this->assertEquals(400.00, $summary['total_earned']); // 300 + 100
        $this->assertEquals(300.00, $summary['pending']); // 3 * 100
        $this->assertEquals(100.00, $summary['paid']); // 2 * 50
        $this->assertEquals(3, $summary['count_pending']);
        $this->assertEquals(2, $summary['count_paid']);
    }

    public function test_can_pay_commission(): void
    {
        $commission = Commission::factory()->create([
            'tenant_id' => $this->operator->tenant_id,
            'reseller_id' => $this->operator->id,
            'status' => 'pending',
        ]);

        $result = $this->commissionService->payCommission($commission, [
            'notes' => 'Paid via bank transfer',
        ]);

        $this->assertTrue($result);
        $commission->refresh();
        $this->assertEquals('paid', $commission->status);
        $this->assertNotNull($commission->paid_at);
    }

    public function test_it_calculates_multi_level_commission_correctly(): void
    {
        config(['commission.rates.sub-operator.direct' => 10.0]);
        config(['commission.rates.operator.direct' => 10.0]);
        config(['commission.rates.sub-operator.parent' => 5.0]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->customer->tenant_id,
            'user_id' => $this->customer->id,
            'amount' => 1000.00,
            'status' => 'completed',
        ]);

        $commissions = $this->commissionService->calculateMultiLevelCommission($payment);

        $this->assertCount(2, $commissions);

        $subOperatorCommission = collect($commissions)->first(function ($c) {
            return $c->reseller_id === $this->subOperator->id;
        });

        $this->assertNotNull($subOperatorCommission);
        $this->assertEquals(100, $subOperatorCommission->commission_amount);

        $operatorCommission = collect($commissions)->first(function ($c) {
            return $c->reseller_id === $this->operator->id;
        });

        $this->assertNotNull($operatorCommission);
        $this->assertEquals(50, $operatorCommission->commission_amount);
    }
}
