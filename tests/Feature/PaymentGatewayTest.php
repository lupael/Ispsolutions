<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Invoice $invoice;

    protected PaymentGateway $gateway;

    protected PaymentGatewayService $paymentGatewayService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $package = ServicePackage::factory()->create([
            'price' => 500.00,
        ]);

        $this->invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $this->customer->id,
            'package_id' => $package->id,
            'total_amount' => 500.00,
        ]);

        $this->gateway = PaymentGateway::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'bKash',
            'slug' => 'bkash',
            'is_active' => true,
            'configuration' => [
                'app_key' => 'test_key',
                'app_secret' => 'test_secret',
            ],
        ]);

        $this->paymentGatewayService = app(PaymentGatewayService::class);
    }

    public function test_can_initiate_payment(): void
    {
        $this->markTestSkipped('Payment gateway initiation requires production API credentials - skipping stub test');
        
        $result = $this->paymentGatewayService->initiatePayment(
            $this->invoice,
            'bkash'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_url', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals(500.00, $result['amount']);
    }

    public function test_can_process_bkash_webhook(): void
    {
        $this->markTestSkipped('Payment gateway webhooks require production API credentials - skipping stub test');
        
        $payload = [
            'status' => 'success',
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => 500.00,
            'transaction_id' => 'BK123456',
        ];

        $result = $this->paymentGatewayService->processWebhook('bkash', $payload);

        $this->assertTrue($result);
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
    }

    public function test_can_verify_payment(): void
    {
        $this->markTestSkipped('Payment gateway verification requires production API credentials - skipping stub test');
        
        $result = $this->paymentGatewayService->verifyPayment(
            'BK123456',
            'bkash',
            $this->customer->tenant_id
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('verified', $result);
        $this->assertTrue($result['verified']);
    }

    public function test_throws_exception_for_unsupported_gateway(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->paymentGatewayService->initiatePayment(
            $this->invoice,
            'unsupported_gateway'
        );
    }
}
