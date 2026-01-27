<?php

namespace App\Services;

use App\Mail\InvoiceExpiringSoon;
use App\Mail\InvoiceGenerated;
use App\Mail\InvoiceOverdue;
use App\Mail\PaymentReceived;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected ?SmsService $smsService;

    public function __construct(?SmsService $smsService = null)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send invoice generated notification
     */
    public function sendInvoiceGeneratedNotification(Invoice $invoice): bool
    {
        $emailSent = false;
        $smsSent = false;

        try {
            if ($invoice->user && $invoice->user->email) {
                Mail::to($invoice->user->email)
                    ->send(new InvoiceGenerated($invoice));

                Log::info('Invoice generated email sent', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                ]);

                $emailSent = true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send invoice generated email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS if enabled
        if (config('sms.enabled', false) && $this->smsService) {
            $smsSent = $this->smsService->sendInvoiceGeneratedSms($invoice);
        }

        return $emailSent || $smsSent;
    }

    /**
     * Send payment received notification
     */
    public function sendPaymentReceivedNotification(Invoice $invoice, int $amount): bool
    {
        $emailSent = false;
        $smsSent = false;

        try {
            if ($invoice->user && $invoice->user->email) {
                // Create a temporary payment object for the email template
                // Note: This is not persisted to the database
                $payment = new Payment([
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'user_id' => $invoice->user_id,
                    'tenant_id' => $invoice->tenant_id,
                ]);
                $payment->invoice = $invoice;
                $payment->user = $invoice->user;

                Mail::to($invoice->user->email)
                    ->send(new PaymentReceived($payment));

                Log::info('Payment received email sent', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'amount' => $amount,
                ]);

                $emailSent = true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment received email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS if enabled
        if (config('sms.enabled', false) && $this->smsService) {
            // Create a temporary payment object for SMS
            // Note: This is not persisted to the database
            $payment = new Payment(['amount' => $amount]);
            $payment->invoice = $invoice;
            $smsSent = $this->smsService->sendPaymentReceivedSms($payment);
        }

        return $emailSent || $smsSent;
    }

    /**
     * Send invoice overdue notification
     */
    public function sendInvoiceOverdue(Invoice $invoice): bool
    {
        $emailSent = false;
        $smsSent = false;

        try {
            if ($invoice->user && $invoice->user->email) {
                Mail::to($invoice->user->email)
                    ->send(new InvoiceOverdue($invoice));

                Log::info('Invoice overdue email sent', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                ]);

                $emailSent = true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send invoice overdue email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS if enabled
        if (config('sms.enabled', false) && $this->smsService) {
            $smsSent = $this->smsService->sendInvoiceOverdueSms($invoice);
        }

        return $emailSent || $smsSent;
    }

    /**
     * Send invoice expiring soon notification
     */
    public function sendInvoiceExpiringSoon(Invoice $invoice, int $daysUntilExpiry): bool
    {
        $emailSent = false;
        $smsSent = false;

        try {
            if ($invoice->user && $invoice->user->email) {
                Mail::to($invoice->user->email)
                    ->send(new InvoiceExpiringSoon($invoice, $daysUntilExpiry));

                Log::info('Invoice expiring soon email sent', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'days_until_expiry' => $daysUntilExpiry,
                ]);

                $emailSent = true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send invoice expiring soon email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS if enabled
        if (config('sms.enabled', false) && $this->smsService) {
            $smsSent = $this->smsService->sendInvoiceExpiringSoonSms($invoice, $daysUntilExpiry);
        }

        return $emailSent || $smsSent;
    }

    /**
     * Get invoices that will expire in N days
     */
    public function getPreExpirationInvoices(int $days): \Illuminate\Database\Eloquent\Collection
    {
        $targetDate = now()->addDays($days)->format('Y-m-d');

        return Invoice::whereDate('due_date', $targetDate)
            ->where('status', 'pending')
            ->with(['user', 'package'])
            ->get();
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('status', 'overdue')
            ->whereDate('due_date', '<', now())
            ->with(['user', 'package'])
            ->get();
    }

    /**
     * Send bulk pre-expiration notifications
     */
    public function sendBulkPreExpirationNotifications(array $invoices): array
    {
        $results = [];

        foreach ($invoices as $invoice) {
            // Calculate days until expiry, ensuring non-negative value
            $daysUntilExpiry = now()->diffInDays($invoice->due_date, false);
            // Use max(0, ...) to handle past dates as 0 days
            $normalizedDays = max(0, (int) $daysUntilExpiry);
            $sent = $this->sendInvoiceExpiringSoon($invoice, $normalizedDays);

            $results[] = [
                'invoice_id' => $invoice->id,
                'sent' => $sent,
            ];
        }

        return $results;
    }

    /**
     * Send pre-expiration reminders for invoices expiring in N days
     */
    public function sendPreExpirationReminders(int $daysBeforeExpiry = 3): int
    {
        $expiringInvoices = $this->getPreExpirationInvoices($daysBeforeExpiry);

        $count = 0;
        foreach ($expiringInvoices as $invoice) {
            if ($this->sendInvoiceExpiringSoon($invoice, $daysBeforeExpiry)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send overdue notifications for all overdue invoices
     */
    public function sendOverdueNotifications(): int
    {
        $overdueInvoices = $this->getOverdueInvoices();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            // Check if notification was sent recently (avoid spam)
            $lastNotificationKey = "overdue_notification_{$invoice->id}";
            $lastSent = cache($lastNotificationKey);

            if (! $lastSent || $lastSent->diffInDays(now()) >= 7) {
                if ($this->sendInvoiceOverdue($invoice)) {
                    cache([$lastNotificationKey => now()], now()->addDays(7));
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Queue notifications (for better performance)
     */
    public function queueInvoiceGenerated(Invoice $invoice): void
    {
        if ($invoice->user && $invoice->user->email) {
            Mail::to($invoice->user->email)
                ->queue(new InvoiceGenerated($invoice));
        }
    }

    /**
     * Queue payment notification
     */
    public function queuePaymentReceived(Payment $payment): void
    {
        if ($payment->user && $payment->user->email) {
            Mail::to($payment->user->email)
                ->queue(new PaymentReceived($payment));
        }
    }
}
