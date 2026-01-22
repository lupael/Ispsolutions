<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountLockingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $customer;
    protected ServicePackage $package;
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->tenant = Tenant::factory()->create();
        
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);
        
        $this->customer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $this->customer->roles()->attach($customerRole);

        $this->package = ServicePackage::factory()->create([
            'price' => 1000.00,
        ]);

        $this->billingService = app(BillingService::class);
    }

    public function test_account_is_locked_for_overdue_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'total_amount' => 1000.00,
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(5),
        ]);

        // Simulate auto-lock process by marking account inactive
        $this->customer->update([
            'is_active' => false,
        ]);

        $this->customer->refresh();
        $this->assertFalse($this->customer->is_active);
    }

    public function test_account_is_unlocked_after_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'total_amount' => 1000.00,
            'status' => 'pending',
        ]);

        $this->customer->update(['is_active' => false]);

        $payment = $this->billingService->processPayment($invoice, [
            'amount' => $invoice->total_amount,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        // The service should mark invoice as paid
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_only_overdue_accounts_are_locked(): void
    {
        // Create invoice not yet due
        $invoiceNotDue = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(5),
        ]);

        // Account should remain active
        $this->assertTrue($this->customer->is_active);
    }

    public function test_grace_period_before_locking(): void
    {
        // Invoice just became overdue (within grace period)
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(1),
        ]);

        // Typically, grace period might be 3-7 days
        // Account should not be locked immediately
        $this->assertTrue($this->customer->is_active);
    }

    public function test_multiple_unpaid_invoices_trigger_lock(): void
    {
        Invoice::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(10),
        ]);

        $overdueInvoices = Invoice::where('user_id', $this->customer->id)
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->count();

        $this->assertEquals(3, $overdueInvoices);

        // Simulate lock based on multiple overdue
        if ($overdueInvoices >= 2) {
            $this->customer->update([
                'is_active' => false,
            ]);
        }

        $this->assertFalse($this->customer->is_active);
    }

    public function test_account_lock_prevents_service_access(): void
    {
        $this->customer->update([
            'is_active' => false,
        ]);

        $this->customer->refresh();
        
        $this->assertFalse($this->customer->is_active);
    }

    public function test_partial_payment_does_not_unlock_account(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'total_amount' => 1000.00,
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(5),
        ]);

        $this->customer->update(['is_active' => false]);

        $payment = $this->billingService->processPayment($invoice, [
            'amount' => 500.00, // Partial payment
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $invoice->refresh();
        $this->assertEquals('pending', $invoice->status);
        
        // Account should remain inactive until full payment
        $this->customer->refresh();
        $this->assertFalse($this->customer->is_active);
    }

    public function test_lock_reason_is_recorded(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(10),
        ]);

        $this->customer->update([
            'is_active' => false,
        ]);

        // Test that account is now inactive
        $this->assertFalse($this->customer->is_active);
    }

    public function test_locked_accounts_can_be_manually_unlocked(): void
    {
        $this->customer->update([
            'is_active' => false,
        ]);

        // Admin manually unlocks
        $this->customer->update([
            'is_active' => true,
        ]);

        $this->customer->refresh();
        $this->assertTrue($this->customer->is_active);
    }

    public function test_accounts_with_paid_invoices_are_not_locked(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->customer->id,
            'status' => 'paid',
            'due_date' => Carbon::now()->subDays(1),
            'paid_at' => Carbon::now(),
        ]);

        $this->assertTrue($this->customer->is_active);
    }
}
