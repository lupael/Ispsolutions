<?php

namespace Tests\Feature;

use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected ServicePackage $monthlyPackage;
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $this->monthlyPackage = ServicePackage::factory()->create([
            'name' => 'Monthly Package',
            'price' => 1000.00,
            'billing_type' => 'monthly',
        ]);

        $this->billingService = app(BillingService::class);
    }

    public function test_can_generate_monthly_invoice(): void
    {
        $invoice = $this->billingService->generateMonthlyInvoice(
            $this->customer,
            $this->monthlyPackage
        );

        $this->assertNotNull($invoice);
        $this->assertEquals($this->customer->id, $invoice->user_id);
        $this->assertEquals($this->monthlyPackage->id, $invoice->package_id);
        $this->assertEquals(1000.00, $invoice->amount);
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_monthly_invoice_period_is_one_month(): void
    {
        $invoice = $this->billingService->generateMonthlyInvoice(
            $this->customer,
            $this->monthlyPackage
        );

        $periodStart = $invoice->billing_period_start;
        $periodEnd = $invoice->billing_period_end;

        // Check that period is approximately 1 month (28-31 days)
        $daysDiff = $periodStart->diffInDays($periodEnd);
        $this->assertGreaterThanOrEqual(28, $daysDiff);
        $this->assertLessThanOrEqual(31, $daysDiff);
    }

    public function test_monthly_invoice_has_grace_period(): void
    {
        $invoice = $this->billingService->generateMonthlyInvoice(
            $this->customer,
            $this->monthlyPackage
        );

        $periodEnd = $invoice->billing_period_end;
        $dueDate = $invoice->due_date;

        // Grace period should be exactly 7 calendar days after the period end
        $expectedDueDate = $periodEnd->copy()->addDays(7);
        $this->assertEquals($expectedDueDate->toDateString(), $dueDate->toDateString());
    }

    public function test_mark_overdue_invoices(): void
    {
        $invoice = $this->billingService->generateMonthlyInvoice(
            $this->customer,
            $this->monthlyPackage
        );

        // Set due date to past
        $invoice->update(['due_date' => now()->subDay()]);

        $count = $this->billingService->markOverdueInvoices();

        $this->assertGreaterThan(0, $count);
        $invoice->refresh();
        $this->assertEquals('overdue', $invoice->status);
    }
}
