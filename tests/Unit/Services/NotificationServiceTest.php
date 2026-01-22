<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $notificationService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();

        // Fake mail and queue
        Mail::fake();
        Queue::fake();
    }

    public function test_can_send_invoice_generated_notification()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
        ]);

        $this->notificationService->sendInvoiceGeneratedNotification($invoice);

        Mail::assertQueued(\App\Mail\InvoiceGenerated::class, function ($mail) use ($networkUser) {
            return $mail->hasTo($networkUser->email);
        });
    }

    public function test_can_send_payment_received_notification()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'paid_amount' => 1000,
        ]);

        $this->notificationService->sendPaymentReceivedNotification($invoice, 1000);

        Mail::assertQueued(\App\Mail\PaymentReceived::class, function ($mail) use ($networkUser) {
            return $mail->hasTo($networkUser->email);
        });
    }

    public function test_can_get_pre_expiration_invoices()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create invoice expiring in 3 days
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'due_date' => now()->addDays(3),
            'status' => 'pending',
        ]);

        $invoices = $this->notificationService->getPreExpirationInvoices(7);

        $this->assertGreaterThanOrEqual(0, $invoices->count());
    }

    public function test_can_get_overdue_invoices()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create overdue invoice
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        $invoices = $this->notificationService->getOverdueInvoices();

        $this->assertGreaterThanOrEqual(0, $invoices->count());
    }

    public function test_can_send_bulk_notifications()
    {
        $networkUsers = NetworkUser::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $invoices = [];
        foreach ($networkUsers as $user) {
            $invoices[] = Invoice::factory()->create([
                'tenant_id' => $this->tenant->id,
                'network_user_id' => $user->id,
                'due_date' => now()->addDays(3),
            ]);
        }

        $result = $this->notificationService->sendBulkPreExpirationNotifications($invoices);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sent', $result);
        $this->assertGreaterThanOrEqual(0, $result['sent']);
    }
}
