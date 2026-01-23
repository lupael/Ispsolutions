<?php

namespace Tests\Unit\Services;

use App\Models\Commission;
use App\Models\Invoice;
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

    protected CommissionService $commissionService;

    protected Tenant $tenant;

    protected User $operator;

    protected User $subOperator;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $operatorRole = Role::factory()->create(['name' => 'operator', 'level' => 50]);
        $subOperatorRole = Role::factory()->create(['name' => 'sub-operator', 'level' => 40]);
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->operator = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->operator->roles()->attach($operatorRole);

        $this->subOperator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
        ]);
        $this->subOperator->roles()->attach($subOperatorRole);

        $this->customer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
        ]);
        $this->customer->roles()->attach($customerRole);

        $this->commissionService = app(CommissionService::class);
    }

    public function test_calculates_commission_for_operator_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'total_amount' => 1000.00,
        ]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
            'status' => 'completed',
        ]);

        $commission = $this->commissionService->calculateCommission($payment);

        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals($this->operator->id, $commission->reseller_id);
        $this->assertEquals(100.00, $commission->commission_amount); // 10% of 1000
        $this->assertEquals(10.0, $commission->commission_percentage);
    }

    public function test_returns_null_for_customer_without_operator(): void
    {
        $customerNoOperator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => null,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customerNoOperator->id,
        ]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customerNoOperator->id,
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
        ]);

        $commission = $this->commissionService->calculateCommission($payment);

        $this->assertNull($commission);
    }

    public function test_calculates_correct_rate_for_sub_operator(): void
    {
        $customer2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->subOperator->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customer2->id,
            'total_amount' => 1000.00,
        ]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customer2->id,
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
        ]);

        $commission = $this->commissionService->calculateCommission($payment);

        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals(50.00, $commission->commission_amount); // 5% of 1000
        $this->assertEquals(5.0, $commission->commission_percentage);
    }

    public function test_can_pay_commission(): void
    {
        $commission = Commission::factory()->create([
            'tenant_id' => $this->tenant->id,
            'reseller_id' => $this->operator->id,
            'commission_amount' => 100.00,
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

    public function test_gets_operator_commission_summary(): void
    {
        Commission::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'reseller_id' => $this->operator->id,
            'commission_amount' => 100.00,
            'status' => 'pending',
        ]);

        Commission::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'reseller_id' => $this->operator->id,
            'commission_amount' => 50.00,
            'status' => 'paid',
        ]);

        $summary = $this->commissionService->getOperatorCommissionSummary($this->operator);

        $this->assertIsArray($summary);
        $this->assertEquals(400.00, $summary['total_earned']); // 300 + 100
        $this->assertEquals(300.00, $summary['pending']);
        $this->assertEquals(100.00, $summary['paid']);
        $this->assertEquals(3, $summary['count_pending']);
        $this->assertEquals(2, $summary['count_paid']);
    }

    public function test_multi_level_commission_calculation(): void
    {
        $customer2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->subOperator->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customer2->id,
            'total_amount' => 2000.00,
        ]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customer2->id,
            'invoice_id' => $invoice->id,
            'amount' => 2000.00,
        ]);

        $commissions = $this->commissionService->calculateMultiLevelCommission($payment);

        $this->assertIsArray($commissions);
        $this->assertCount(2, $commissions); // Sub-operator + Operator
    }
}
