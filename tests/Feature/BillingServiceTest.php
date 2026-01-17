<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected ServicePackage $package;
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        
        $adminRole = Role::factory()->create(['name' => 'admin', 'level' => 90]);
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $this->package = ServicePackage::factory()->create([
            'name' => 'Test Package',
            'price' => 1000.00,
        ]);

        $this->billingService = app(BillingService::class);
    }

    public function test_can_generate_invoice_for_user(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($this->customer->id, $invoice->user_id);
        $this->assertEquals($this->package->id, $invoice->package_id);
        $this->assertEquals(1000.00, $invoice->amount);
        $this->assertEquals('pending', $invoice->status);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_can_process_payment_for_invoice(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $payment = $this->billingService->processPayment($invoice, [
            'amount' => $invoice->total_amount,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($invoice->id, $payment->invoice_id);
        $this->assertEquals('completed', $payment->status);
        
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_invoice_marked_as_overdue_after_due_date(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->customer->tenant_id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        $count = $this->billingService->markOverdueInvoices();

        $this->assertGreaterThan(0, $count);
        $invoice->refresh();
        $this->assertEquals('overdue', $invoice->status);
    }
}
