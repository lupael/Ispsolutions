<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Log SMS to database
     */
    protected function logSms(
        string $phoneNumber,
        string $message,
        string $status,
        ?int $smsGatewayId = null,
        ?int $userId = null,
        ?array $gatewayResponse = null
    ): SmsLog {
        $tenantId = auth()->user()->tenant_id ?? null;
        
        if ($tenantId === null) {
            throw new \Exception('Cannot log SMS: No tenant context available');
        }

        return SmsLog::create([
            'tenant_id' => $tenantId,
            'sms_gateway_id' => $smsGatewayId,
            'user_id' => $userId,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'status' => $status,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Send SMS using template
     */
    public function sendFromTemplate(string $templateSlug, string $phoneNumber, array $data, ?int $userId = null): bool
    {
        $template = SmsTemplate::active()->bySlug($templateSlug)->first();

        if (!$template) {
            Log::warning('SMS template not found', ['slug' => $templateSlug]);
            return false;
        }

        $message = $template->render($data);
        return $this->sendSms($phoneNumber, $message, null, $userId);
    }

    /**
     * Get SMS gateway by tenant
     */
    protected function getActiveGateway(?int $tenantId = null): ?SmsGateway
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id ?? 1;
        return SmsGateway::where('tenant_id', $tenantId)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Send SMS via configured gateway
     */
    public function sendSms(string $phoneNumber, string $message, ?string $gateway = null, ?int $userId = null): bool
    {
        $gateway = $gateway ?? config('sms.default_gateway', 'twilio');
        $smsLog = null;

        try {
            // Create SMS log
            $smsLog = $this->logSms($phoneNumber, $message, SmsLog::STATUS_PENDING, null, $userId);

            $result = match ($gateway) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $message),
                'nexmo' => $this->sendViaNexmo($phoneNumber, $message),
                'bulksms' => $this->sendViaBulkSms($phoneNumber, $message),
                'bangladeshi' => $this->sendViaBangladeshiGateway($phoneNumber, $message),
                default => throw new \Exception("Unsupported SMS gateway: {$gateway}"),
            };

            // Update log status
            if ($result && $smsLog) {
                $smsLog->markAsSent();
            } elseif ($smsLog) {
                $smsLog->markAsFailed('Gateway returned false');
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            if ($smsLog) {
                $smsLog->markAsFailed($e->getMessage());
            }

            return false;
        }
    }

    /**
     * Send invoice generated SMS
     */
    public function sendInvoiceGeneratedSms(Invoice $invoice): bool
    {
        if (! $invoice->user || ! $invoice->user->phone) {
            return false;
        }

        $message = sprintf(
            'New invoice %s generated. Amount: %.2f. Due: %s. Pay at: %s',
            $invoice->invoice_number,
            $invoice->total_amount,
            $invoice->due_date->format('Y-m-d'),
            config('app.url') . '/payments/invoices/' . $invoice->id
        );

        return $this->sendSms($invoice->user->phone, $message);
    }

    /**
     * Send payment received SMS
     */
    public function sendPaymentReceivedSms(Payment $payment): bool
    {
        if (! $payment->user || ! $payment->user->phone) {
            return false;
        }

        $message = sprintf(
            'Payment received! Amount: %.2f. Transaction: %s. Thank you!',
            $payment->amount,
            $payment->payment_number
        );

        return $this->sendSms($payment->user->phone, $message);
    }

    /**
     * Send invoice expiring soon SMS
     */
    public function sendInvoiceExpiringSoonSms(Invoice $invoice, int $daysUntilExpiry): bool
    {
        if (! $invoice->user || ! $invoice->user->phone) {
            return false;
        }

        $message = sprintf(
            'Reminder: Invoice %s expires in %d day(s). Amount: %.2f. Please pay soon.',
            $invoice->invoice_number,
            $daysUntilExpiry,
            $invoice->total_amount
        );

        return $this->sendSms($invoice->user->phone, $message);
    }

    /**
     * Send invoice overdue SMS
     */
    public function sendInvoiceOverdueSms(Invoice $invoice): bool
    {
        if (! $invoice->user || ! $invoice->user->phone) {
            return false;
        }

        $message = sprintf(
            'URGENT: Invoice %s is overdue. Amount: %.2f. Please pay immediately to avoid service interruption.',
            $invoice->invoice_number,
            $invoice->total_amount
        );

        return $this->sendSms($invoice->user->phone, $message);
    }

    /**
     * Send OTP SMS
     */
    public function sendOtpSms(string $phoneNumber, string $otpCode): bool
    {
        $message = sprintf(
            'Your OTP code is: %s. Valid for 10 minutes. Do not share this code with anyone.',
            $otpCode
        );

        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        $accountSid = config('sms.twilio.account_sid');
        $authToken = config('sms.twilio.auth_token');
        $fromNumber = config('sms.twilio.from_number');

        if (! $accountSid || ! $authToken || ! $fromNumber) {
            Log::warning('Twilio credentials not configured');

            return false;
        }

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $fromNumber,
                'To' => $phoneNumber,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            Log::info('SMS sent via Twilio', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Twilio SMS failed', [
            'phone' => $phoneNumber,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }

    /**
     * Send via Nexmo/Vonage
     */
    protected function sendViaNexmo(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.nexmo.api_key');
        $apiSecret = config('sms.nexmo.api_secret');
        $fromNumber = config('sms.nexmo.from_number');

        if (! $apiKey || ! $apiSecret) {
            Log::warning('Nexmo credentials not configured');

            return false;
        }

        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $fromNumber,
            'to' => $phoneNumber,
            'text' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['messages'][0]['status']) && $data['messages'][0]['status'] === '0') {
                Log::info('SMS sent via Nexmo', ['phone' => $phoneNumber]);

                return true;
            }
        }

        Log::error('Nexmo SMS failed', [
            'phone' => $phoneNumber,
            'response' => $response->body(),
        ]);

        return false;
    }

    /**
     * Send via BulkSMS
     */
    protected function sendViaBulkSms(string $phoneNumber, string $message): bool
    {
        $username = config('sms.bulksms.username');
        $password = config('sms.bulksms.password');

        if (! $username || ! $password) {
            Log::warning('BulkSMS credentials not configured');

            return false;
        }

        $response = Http::withBasicAuth($username, $password)
            ->asJson()
            ->post('https://api.bulksms.com/v1/messages', [
                'to' => $phoneNumber,
                'body' => $message,
            ]);

        if ($response->successful()) {
            Log::info('SMS sent via BulkSMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('BulkSMS failed', [
            'phone' => $phoneNumber,
            'response' => $response->body(),
        ]);

        return false;
    }

    /**
     * Send via Bangladeshi SMS Gateway (generic implementation)
     */
    protected function sendViaBangladeshiGateway(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.bangladeshi.api_key');
        $senderId = config('sms.bangladeshi.sender_id');
        $apiUrl = config('sms.bangladeshi.api_url');

        if (! $apiKey || ! $senderId || ! $apiUrl) {
            Log::warning('Bangladeshi SMS gateway not configured');

            return false;
        }

        // Generic format for Bangladeshi SMS gateways
        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'sender_id' => $senderId,
            'phone' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Bangladeshi gateway', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Bangladeshi SMS gateway failed', [
            'phone' => $phoneNumber,
            'response' => $response->body(),
        ]);

        return false;
    }

    /**
     * Send bulk SMS to multiple recipients
     */
    public function sendBulkSms(array $phoneNumbers, string $message, ?string $gateway = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($phoneNumbers as $phoneNumber) {
            $sent = $this->sendSms($phoneNumber, $message, $gateway);

            if ($sent) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][$phoneNumber] = $sent;
        }

        return $results;
    }
}
