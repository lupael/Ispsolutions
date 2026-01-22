<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CommissionService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected User $customer;

    protected ServicePackage $package;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $this->package = ServicePackage::factory()->create([
            'price' => 1000.00,
            'billing_cycle' => 'monthly',
        ]);

        // Mock NotificationService to prevent actual notifications
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('queueInvoiceGenerated')->andReturn(true);
            $mock->shouldReceive('queuePaymentReceived')->andReturn(true);
        });

        // Mock CommissionService
        $this->mock(CommissionService::class, function ($mock) {
            $mock->shouldReceive('calculateMultiLevelCommission')->andReturn([]);
        });

        $this->billingService = app(BillingService::class);
    }

    public function test_can_generate_invoice_for_customer(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($this->customer->id, $invoice->user_id);
        $this->assertEquals($this->package->id, $invoice->package_id);
        $this->assertEquals(1000.00, $invoice->amount);
        $this->assertEquals('pending', $invoice->status);
        $this->assertNotNull($invoice->invoice_number);
    }

    public function test_invoice_calculates_tax_correctly(): void
    {
        config(['billing.tax_rate' => 15]);

        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $expectedTax = 1000.00 * 0.15;
        $this->assertEquals($expectedTax, $invoice->tax_amount);
        $this->assertEquals(1000.00 + $expectedTax, $invoice->total_amount);
    }

    public function test_invoice_number_is_unique(): void
    {
        $invoice1 = $this->billingService->generateInvoice($this->customer, $this->package);
        $invoice2 = $this->billingService->generateInvoice($this->customer, $this->package);

        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
    }

    public function test_can_process_payment_for_invoice(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $paymentData = [
            'amount' => $invoice->total_amount,
            'method' => 'cash',
            'status' => 'completed',
            'transaction_id' => 'TXN123456',
        ];

        $payment = $this->billingService->processPayment($invoice, $paymentData);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($invoice->id, $payment->invoice_id);
        $this->assertEquals($invoice->total_amount, $payment->amount);
        $this->assertNotNull($payment->payment_number);
    }

    public function test_invoice_status_updates_to_paid_when_fully_paid(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $paymentData = [
            'amount' => $invoice->total_amount,
            'method' => 'cash',
            'status' => 'completed',
        ];

        $this->billingService->processPayment($invoice, $paymentData);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_partial_payment_does_not_mark_invoice_as_paid(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $paymentData = [
            'amount' => $invoice->total_amount / 2,
            'method' => 'cash',
            'status' => 'completed',
        ];

        $this->billingService->processPayment($invoice, $paymentData);

        $invoice->refresh();
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_multiple_partial_payments_mark_invoice_as_paid(): void
    {
        $invoice = $this->billingService->generateInvoice($this->customer, $this->package);

        $paymentData1 = [
            'amount' => $invoice->total_amount / 2,
            'method' => 'cash',
            'status' => 'completed',
        ];

        $paymentData2 = [
            'amount' => $invoice->total_amount / 2,
            'method' => 'cash',
            'status' => 'completed',
        ];

        $this->billingService->processPayment($invoice, $paymentData1);
        $this->billingService->processPayment($invoice, $paymentData2);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
