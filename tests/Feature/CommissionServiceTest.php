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

        $adminRole = Role::factory()->create(['name' => 'admin', 'level' => 90]);
        $resellerRole = Role::factory()->create(['name' => 'reseller', 'level' => 60]);
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->admin->roles()->attach($adminRole);

        $this->reseller = User::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $this->admin->id,
        ]);
        $this->reseller->roles()->attach($resellerRole);

        $this->customer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $this->reseller->id,
        ]);
        $this->customer->roles()->attach($customerRole);

        $this->commissionService = app(CommissionService::class);
    }

    public function test_can_calculate_commission_for_reseller(): void
    {
        $payment = Payment::factory()->create([
            'tenant_id' => $this->customer->tenant_id,
            'user_id' => $this->customer->id,
            'amount' => 1000.00,
            'status' => 'completed',
        ]);

        $commission = $this->commissionService->calculateCommission($payment);

        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals($this->reseller->id, $commission->reseller_id);
        $this->assertEquals($payment->id, $commission->payment_id);
        $this->assertEquals(100.00, $commission->commission_amount); // 10% of 1000
        $this->assertEquals(10.0, $commission->commission_percentage);
        $this->assertEquals('pending', $commission->status);
    }

    public function test_can_get_reseller_commission_summary(): void
    {
        Commission::factory()->count(3)->create([
            'tenant_id' => $this->reseller->tenant_id,
            'reseller_id' => $this->reseller->id,
            'commission_amount' => 100.00,
            'status' => 'pending',
        ]);

        Commission::factory()->count(2)->create([
            'tenant_id' => $this->reseller->tenant_id,
            'reseller_id' => $this->reseller->id,
            'commission_amount' => 50.00,
            'status' => 'paid',
        ]);

        $summary = $this->commissionService->getOperatorCommissionSummary($this->reseller);

        $this->assertEquals(400.00, $summary['total_earned']); // 300 + 100
        $this->assertEquals(300.00, $summary['pending']); // 3 * 100
        $this->assertEquals(100.00, $summary['paid']); // 2 * 50
        $this->assertEquals(3, $summary['count_pending']);
        $this->assertEquals(2, $summary['count_paid']);
    }

    public function test_can_pay_commission(): void
    {
        $commission = Commission::factory()->create([
            'tenant_id' => $this->reseller->tenant_id,
            'reseller_id' => $this->reseller->id,
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
}
