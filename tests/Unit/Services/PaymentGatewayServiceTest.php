<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Tenant;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentGatewayService $paymentGatewayService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGatewayService = new PaymentGatewayService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();

        // Fake HTTP requests
        Http::fake();
    }

    public function test_can_initiate_bkash_payment()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
        ]);

        $result = $this->paymentGatewayService->initiatePayment('bkash', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_initiate_nagad_payment()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
        ]);

        $result = $this->paymentGatewayService->initiatePayment('nagad', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_initiate_sslcommerz_payment()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
        ]);

        $result = $this->paymentGatewayService->initiatePayment('sslcommerz', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_initiate_stripe_payment()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
        ]);

        $result = $this->paymentGatewayService->initiatePayment('stripe', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_handles_unsupported_gateway()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'total_amount' => 1000,
        ]);

        $result = $this->paymentGatewayService->initiatePayment('unsupported', $invoice, 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
    }

    public function test_can_verify_payment()
    {
        $result = $this->paymentGatewayService->verifyPayment('bkash', 'test-transaction-id');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_process_webhook()
    {
        $webhookData = [
            'transaction_id' => 'test-txn-123',
            'status' => 'success',
            'amount' => 1000,
        ];

        $result = $this->paymentGatewayService->processWebhook('bkash', $webhookData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
}
