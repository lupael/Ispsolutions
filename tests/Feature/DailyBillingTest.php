<?php

namespace Tests\Feature;

use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected ServicePackage $dailyPackage;
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $this->dailyPackage = ServicePackage::factory()->create([
            'name' => 'Daily Package',
            'price' => 300.00, // Monthly price
            'billing_type' => 'daily',
            'validity_days' => 7,
        ]);

        $this->billingService = app(BillingService::class);
    }

    public function test_can_generate_daily_invoice(): void
    {
        $invoice = $this->billingService->generateDailyInvoice(
            $this->customer,
            $this->dailyPackage,
            7
        );

        $this->assertNotNull($invoice);
        $this->assertEquals($this->customer->id, $invoice->user_id);
        $this->assertEquals($this->dailyPackage->id, $invoice->package_id);
        
        // Pro-rated for 7 days: (300 / 30) * 7 = 70
        $this->assertEquals(70.00, $invoice->amount);
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_daily_invoice_period_is_correct(): void
    {
        $invoice = $this->billingService->generateDailyInvoice(
            $this->customer,
            $this->dailyPackage,
            7
        );

        $periodStart = $invoice->billing_period_start;
        $periodEnd = $invoice->billing_period_end;

        // For 7 days of service: day 1 through day 7 inclusive
        // Start: day 1 at 00:00, End: day 7 at 23:59:59
        // diffInDays counts complete 24-hour periods between timestamps
        $daysDiff = $periodStart->diffInDays($periodEnd);
        $this->assertEquals(6, $daysDiff); // 6 complete days between start and end dates
    }

    public function test_daily_invoice_with_custom_validity(): void
    {
        $invoice = $this->billingService->generateDailyInvoice(
            $this->customer,
            $this->dailyPackage,
            15
        );

        // Pro-rated for 15 days: (300 / 30) * 15 = 150
        $this->assertEquals(150.00, $invoice->amount);
    }

    public function test_lock_expired_accounts(): void
    {
        // Create an expired invoice
        $invoice = $this->billingService->generateDailyInvoice(
            $this->customer,
            $this->dailyPackage,
            1
        );

        // Manually set due date to past
        $invoice->update([
            'due_date' => now()->subDays(1),
            'status' => 'overdue',
        ]);

        $this->customer->update(['is_active' => true]);

        $count = $this->billingService->lockExpiredAccounts();

        $this->assertGreaterThan(0, $count);
        $this->customer->refresh();
        $this->assertFalse($this->customer->is_active);
    }

    public function test_unlock_account_on_payment(): void
    {
        // Create invoice and lock account
        $invoice = $this->billingService->generateDailyInvoice(
            $this->customer,
            $this->dailyPackage,
            1
        );

        $this->customer->update(['is_active' => false]);

        // Process payment
        $this->billingService->processPayment($invoice, [
            'amount' => $invoice->total_amount,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $this->customer->refresh();
        $this->assertTrue($this->customer->is_active);
    }
}
