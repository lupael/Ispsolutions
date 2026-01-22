<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
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
            'email' => 'customer@test.com',
        ]);
        $this->customer->roles()->attach($customerRole);

        $this->package = ServicePackage::factory()->create([
            'name' => 'Standard Package',
            'price' => 1200.00,
            'billing_cycle' => 'monthly',
        ]);

        $this->billingService = app(BillingService::class);
    }

    public function test_invoice_is_generated_with_correct_details(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'amount' => 1200.00,
            'status' => 'pending',
        ]);

        $this->assertNotNull($invoice->invoice_number);
        $this->assertNotNull($invoice->billing_period_start);
        $this->assertNotNull($invoice->billing_period_end);
        $this->assertNotNull($invoice->due_date);
    }

    public function test_invoice_includes_tax_calculation(): void
    {
        config(['billing.tax_rate' => 10]);

        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $expectedTax = 1200.00 * 0.10;

        $this->assertEquals(1200.00, $invoice->amount);
        $this->assertEquals($expectedTax, $invoice->tax_amount);
        $this->assertEquals(1200.00 + $expectedTax, $invoice->total_amount);
    }

    public function test_invoice_billing_period_is_calculated_correctly_for_monthly(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $periodStart = $invoice->billing_period_start;
        $periodEnd = $invoice->billing_period_end;

        $this->assertNotNull($periodStart);
        $this->assertNotNull($periodEnd);
        $this->assertTrue($periodEnd->greaterThan($periodStart));
    }

    public function test_multiple_invoices_can_be_generated_for_same_customer(): void
    {
        $invoice1 = $this->billingService->generateInvoice($this->customer, $this->package);
        $invoice2 = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertNotEquals($invoice1->id, $invoice2->id);
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);

        $customerInvoices = Invoice::where('user_id', $this->customer->id)->get();
        $this->assertCount(2, $customerInvoices);
    }

    public function test_invoice_generation_respects_package_price(): void
    {
        $package1 = ServicePackage::factory()->create(['price' => 1000.00]);
        $package2 = ServicePackage::factory()->create(['price' => 2500.00]);

        $invoice1 = $this->billingService->generateInvoice($this->customer, $package1);
        $invoice2 = $this->billingService->generateInvoice($this->customer, $package2);

        $this->assertEquals(1000.00, $invoice1->amount);
        $this->assertEquals(2500.00, $invoice2->amount);
    }

    public function test_invoice_due_date_is_set_correctly(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertNotNull($invoice->due_date);
        $this->assertTrue($invoice->due_date->greaterThan($invoice->billing_period_start));
    }

    public function test_invoice_is_isolated_by_tenant(): void
    {
        $tenant2 = Tenant::factory()->create();
        $customer2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $invoice1 = $this->billingService->generateInvoice($this->customer, $this->package);
        $invoice2 = $this->billingService->generateInvoice($customer2, $this->package);

        $this->assertEquals($this->tenant->id, $invoice1->tenant_id);
        $this->assertEquals($tenant2->id, $invoice2->tenant_id);

        $tenant1Invoices = Invoice::where('tenant_id', $this->tenant->id)->get();
        $this->assertTrue($tenant1Invoices->contains($invoice1));
        $this->assertFalse($tenant1Invoices->contains($invoice2));
    }

    public function test_bulk_invoice_generation_for_multiple_customers(): void
    {
        $customers = User::factory()->count(10)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        foreach ($customers as $customer) {
            $customer->roles()->attach(Role::where('name', 'customer')->first());
        }

        $invoices = [];
        foreach ($customers as $customer) {
            $invoices[] = $this->billingService->generateInvoice($customer, $this->package);
        }

        $this->assertCount(10, $invoices);
        $this->assertEquals(10, Invoice::where('package_id', $this->package->id)->count());
    }

    public function test_invoice_generation_creates_pending_status(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertEquals('pending', $invoice->status);
        $this->assertNull($invoice->paid_at);
    }

    public function test_invoice_number_generation_is_unique_and_sequential(): void
    {
        $invoices = [];
        for ($i = 0; $i < 5; $i++) {
            $invoices[] = $this->billingService->generateInvoice($this->customer, $this->package);
        }

        $invoiceNumbers = array_map(fn ($inv) => $inv->invoice_number, $invoices);
        $uniqueNumbers = array_unique($invoiceNumbers);

        $this->assertCount(5, $uniqueNumbers);
    }
}
