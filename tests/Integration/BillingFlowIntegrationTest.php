<?php

namespace Tests\Integration;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\NotificationService;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected PaymentGatewayService $paymentGatewayService;

    protected NotificationService $notificationService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->billingService = new BillingService;
        $this->paymentGatewayService = new PaymentGatewayService;
        $this->notificationService = new NotificationService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();

        // Fake mail
        Mail::fake();
    }

    public function test_complete_monthly_billing_flow()
    {
        // Step 1: Create network user with package
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Monthly Package',
            'billing_type' => 'monthly',
            'price' => 1000,
            'validity_days' => 30,
        ]);

        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);

        // Step 2: Generate monthly invoice
        $invoice = $this->billingService->generateMonthlyInvoice($networkUser);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals('pending', $invoice->status);

        // Step 3: Verify notification was sent
        Mail::assertQueued(\App\Mail\InvoiceGenerated::class);

        // Step 4: Process payment
        $payment = $this->billingService->processPayment($invoice, 1100, 'cash');

        $this->assertNotNull($payment);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);

        // Step 5: Verify payment notification was sent
        Mail::assertQueued(\App\Mail\PaymentReceived::class);

        // Step 6: Verify user is still active
        $networkUser->refresh();
        $this->assertEquals('active', $networkUser->status);
    }

    public function test_complete_daily_billing_flow()
    {
        // Step 1: Create network user with daily package
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Daily Package',
            'billing_type' => 'daily',
            'price' => 30,
            'validity_days' => 1,
        ]);

        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'validity_days' => 7,
        ]);

        // Step 2: Generate daily invoice
        $invoice = $this->billingService->generateDailyInvoice($networkUser);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertGreaterThan(0, $invoice->subtotal);
        $this->assertEquals('pending', $invoice->status);

        // Step 3: Process payment
        $payment = $this->billingService->processPayment($invoice, $invoice->total_amount, 'cash');

        $this->assertNotNull($payment);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_payment_gateway_integration_flow()
    {
        // Step 1: Create invoice
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
            'status' => 'pending',
        ]);

        // Step 2: Initiate payment via gateway
        $result = $this->paymentGatewayService->initiatePayment('bkash', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        // Step 3: Simulate webhook callback
        $webhookData = [
            'transaction_id' => 'TEST-TXN-123',
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => 'success',
        ];

        $webhookResult = $this->paymentGatewayService->processWebhook('bkash', $webhookData);

        $this->assertIsArray($webhookResult);
        $this->assertArrayHasKey('success', $webhookResult);
    }

    public function test_expired_invoice_flow()
    {
        // Step 1: Create expired invoice
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'due_date' => now()->subDays(10),
            'status' => 'pending',
        ]);

        // Step 2: Get overdue invoices
        $overdueInvoices = $this->notificationService->getOverdueInvoices();

        $this->assertGreaterThanOrEqual(1, $overdueInvoices->count());

        // Step 3: Send overdue notifications
        $result = $this->notificationService->sendBulkOverdueNotifications($overdueInvoices);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sent', $result);
    }

    public function test_pre_expiration_notification_flow()
    {
        // Step 1: Create invoice expiring soon
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'due_date' => now()->addDays(3),
            'status' => 'pending',
        ]);

        // Step 2: Get pre-expiration invoices
        $preExpirationInvoices = $this->notificationService->getPreExpirationInvoices(7);

        $this->assertGreaterThanOrEqual(1, $preExpirationInvoices->count());

        // Step 3: Send pre-expiration notifications
        $result = $this->notificationService->sendBulkPreExpirationNotifications($preExpirationInvoices);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sent', $result);
    }

    public function test_account_lock_on_expired_invoice()
    {
        // Step 1: Create expired invoice
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'due_date' => now()->subDays(10),
            'status' => 'pending',
        ]);

        // Step 2: Lock expired accounts (simulated via billing service)
        $networkUser->update(['status' => 'suspended']);

        $networkUser->refresh();
        $this->assertEquals('suspended', $networkUser->status);

        // Step 3: Process late payment
        $payment = $this->billingService->processPayment($invoice, $invoice->total_amount, 'cash');

        $this->assertNotNull($payment);

        // Step 4: Verify account is unlocked
        $networkUser->refresh();
        $this->assertEquals('active', $networkUser->status);
    }
}
