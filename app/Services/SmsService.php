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
     * Log SMS to database (masks OTP codes for security)
     */
    protected function logSms(
        string $phoneNumber,
        string $message,
        string $status,
        ?int $smsGatewayId = null,
        ?int $userId = null,
        ?array $gatewayResponse = null,
        ?int $tenantId = null
    ): SmsLog {
        if ($tenantId === null) {
            $user = auth()->user();
            if ($user !== null) {
                $tenantId = $user->tenant_id;
            }
        }

        if ($tenantId === null) {
            throw new \Exception('Cannot log SMS: No tenant context available');
        }

        // Mask OTP codes in message for security
        $maskedMessage = $this->maskOtpInMessage($message);

        return SmsLog::create([
            'tenant_id' => $tenantId,
            'sms_gateway_id' => $smsGatewayId,
            'user_id' => $userId,
            'phone_number' => $phoneNumber,
            'message' => $maskedMessage,
            'status' => $status,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Mask OTP codes in messages to prevent cleartext storage
     */
    protected function maskOtpInMessage(string $message): string
    {
        // Mask patterns like "Your OTP code is: 123456"
        $message = preg_replace('/(\b\d{4,8}\b)/', '****', $message);

        return $message;
    }

    /**
     * Send SMS using template
     */
    public function sendFromTemplate(string $templateSlug, string $phoneNumber, array $data, ?int $userId = null, ?int $tenantId = null): bool
    {
        if ($tenantId === null) {
            $user = auth()->user();
            $tenantId = $user?->tenant_id;
        }

        if ($tenantId === null) {
            Log::warning('Cannot send template SMS: No tenant context available');

            return false;
        }

        $template = SmsTemplate::where('tenant_id', $tenantId)
            ->active()
            ->bySlug($templateSlug)
            ->first();

        if (! $template) {
            Log::warning('SMS template not found', ['slug' => $templateSlug, 'tenant_id' => $tenantId]);

            return false;
        }

        $message = $template->render($data);

        return $this->sendSms($phoneNumber, $message, null, $userId, $tenantId);
    }

    /**
     * Get SMS gateway by tenant
     */
    protected function getActiveGateway(?int $tenantId = null): ?SmsGateway
    {
        if ($tenantId === null) {
            $user = auth()->user();
            $tenantId = $user?->tenant_id;
        }

        if ($tenantId === null) {
            throw new \Exception('Cannot retrieve SMS gateway: No tenant context available');
        }

        return SmsGateway::where('tenant_id', $tenantId)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Send SMS via configured gateway
     */
    public function sendSms(string $phoneNumber, string $message, ?string $gateway = null, ?int $userId = null, ?int $tenantId = null): bool
    {
        $gateway = $gateway ?? config('sms.default_gateway', 'twilio');
        $smsLog = null;

        try {
            // Create SMS log
            $smsLog = $this->logSms($phoneNumber, $message, SmsLog::STATUS_PENDING, null, $userId, null, $tenantId);

            $result = match ($gateway) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $message),
                'nexmo' => $this->sendViaNexmo($phoneNumber, $message),
                'bulksms' => $this->sendViaBulkSms($phoneNumber, $message),
                'bangladeshi' => $this->sendViaBangladeshiGateway($phoneNumber, $message),
                // Bangladeshi SMS gateways
                'maestro' => $this->sendViaMaestro($phoneNumber, $message),
                'robi' => $this->sendViaRobi($phoneNumber, $message),
                'm2mbd' => $this->sendViaM2mbd($phoneNumber, $message),
                'bangladeshsms' => $this->sendViaBangladeshSms($phoneNumber, $message),
                'bulksmsbd' => $this->sendViaBulkSmsBd($phoneNumber, $message),
                'btssms' => $this->sendViaBtsSms($phoneNumber, $message),
                '880sms' => $this->sendVia880Sms($phoneNumber, $message),
                'bdsmartpay' => $this->sendViaBdSmartPay($phoneNumber, $message),
                'elitbuzz' => $this->sendViaElitbuzz($phoneNumber, $message),
                'sslwireless' => $this->sendViaSslWireless($phoneNumber, $message),
                'adnsms' => $this->sendViaAdnSms($phoneNumber, $message),
                '24smsbd' => $this->sendVia24SmsBd($phoneNumber, $message),
                'smsnet' => $this->sendViaSmsNet($phoneNumber, $message),
                'brandsms' => $this->sendViaBrandSms($phoneNumber, $message),
                'metrotel' => $this->sendViaMetrotel($phoneNumber, $message),
                'dianahost' => $this->sendViaDianahost($phoneNumber, $message),
                'smsinbd' => $this->sendViaSmsInBd($phoneNumber, $message),
                'dhakasoftbd' => $this->sendViaDhakasoftBd($phoneNumber, $message),
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

    /**
     * Send via Maestro SMS Gateway
     */
    protected function sendViaMaestro(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.maestro.api_key');
        $senderId = config('sms.maestro.sender_id');
        $apiUrl = config('sms.maestro.api_url', 'https://api.maestrosms.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Maestro SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Maestro', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Maestro SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Robi SMS Gateway
     */
    protected function sendViaRobi(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.robi.api_key');
        $senderId = config('sms.robi.sender_id');
        $apiUrl = config('sms.robi.api_url', 'https://esms.mimsms.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Robi SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Robi', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Robi SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via M2M BD SMS Gateway
     */
    protected function sendViaM2mbd(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.m2mbd.api_key');
        $senderId = config('sms.m2mbd.sender_id');
        $apiUrl = config('sms.m2mbd.api_url', 'https://api.m2mbd.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('M2M BD SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via M2M BD', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('M2M BD SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Bangladesh SMS Gateway
     */
    protected function sendViaBangladeshSms(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.bangladeshsms.api_key');
        $senderId = config('sms.bangladeshsms.sender_id');
        $apiUrl = config('sms.bangladeshsms.api_url', 'https://api.bangladeshsms.com/send');

        if (! $apiKey || ! $senderId) {
            Log::warning('BangladeshSMS credentials not configured');

            return false;
        }

        $response = Http::asForm()->post($apiUrl, [
            'api_key' => $apiKey,
            'sender_id' => $senderId,
            'phone' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via BangladeshSMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('BangladeshSMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Bulk SMS BD Gateway
     */
    protected function sendViaBulkSmsBd(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.bulksmsbd.api_key');
        $senderId = config('sms.bulksmsbd.sender_id');
        $apiUrl = config('sms.bulksmsbd.api_url', 'https://api.bulksmsbd.com/api/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('BulkSMSBD credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'number' => $phoneNumber,
            'senderid' => $senderId,
            'message' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via BulkSMSBD', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('BulkSMSBD failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via BTS SMS Gateway
     */
    protected function sendViaBtsSms(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.btssms.api_key');
        $senderId = config('sms.btssms.sender_id');
        $apiUrl = config('sms.btssms.api_url', 'https://api.btssms.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('BTS SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via BTS SMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('BTS SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via 880 SMS Gateway
     */
    protected function sendVia880Sms(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.880sms.api_key');
        $senderId = config('sms.880sms.sender_id');
        $apiUrl = config('sms.880sms.api_url', 'https://api.880sms.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('880 SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via 880 SMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('880 SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via BD Smart Pay Gateway
     */
    protected function sendViaBdSmartPay(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.bdsmartpay.api_key');
        $senderId = config('sms.bdsmartpay.sender_id');
        $apiUrl = config('sms.bdsmartpay.api_url', 'https://api.bdsmartpay.com/sms');

        if (! $apiKey || ! $senderId) {
            Log::warning('BD Smart Pay SMS credentials not configured');

            return false;
        }

        $response = Http::asForm()->post($apiUrl, [
            'api_key' => $apiKey,
            'senderid' => $senderId,
            'number' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via BD Smart Pay', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('BD Smart Pay SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Elitbuzz SMS Gateway
     */
    protected function sendViaElitbuzz(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.elitbuzz.api_key');
        $senderId = config('sms.elitbuzz.sender_id');
        $apiUrl = config('sms.elitbuzz.api_url', 'https://api.elitbuzz-bd.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Elitbuzz SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Elitbuzz', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Elitbuzz SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via SSL Wireless Gateway
     */
    protected function sendViaSslWireless(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.sslwireless.api_key');
        $sid = config('sms.sslwireless.sid');
        $senderId = config('sms.sslwireless.sender_id');
        $apiUrl = config('sms.sslwireless.api_url', 'https://smsplus.sslwireless.com/api/v3/send-sms');

        if (! $apiKey || ! $sid || ! $senderId) {
            Log::warning('SSL Wireless SMS credentials not configured');

            return false;
        }

        $response = Http::asJson()->post($apiUrl, [
            'api_token' => $apiKey,
            'sid' => $sid,
            'sms' => $message,
            'msisdn' => $phoneNumber,
            'csms_id' => uniqid(),
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via SSL Wireless', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('SSL Wireless SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via ADN SMS Gateway
     */
    protected function sendViaAdnSms(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.adnsms.api_key');
        $senderId = config('sms.adnsms.sender_id');
        $apiUrl = config('sms.adnsms.api_url', 'https://api.adnsms.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('ADN SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via ADN SMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('ADN SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via 24 SMS BD Gateway
     */
    protected function sendVia24SmsBd(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.24smsbd.api_key');
        $senderId = config('sms.24smsbd.sender_id');
        $apiUrl = config('sms.24smsbd.api_url', 'https://api.24smsbd.com/api/v1/SendSMS');

        if (! $apiKey || ! $senderId) {
            Log::warning('24 SMS BD credentials not configured');

            return false;
        }

        $response = Http::asForm()->post($apiUrl, [
            'ApiKey' => $apiKey,
            'SenderId' => $senderId,
            'Message' => $message,
            'MobileNumber' => $phoneNumber,
            'Is_Unicode' => false,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via 24 SMS BD', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('24 SMS BD failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via SMS Net Gateway
     */
    protected function sendViaSmsNet(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.smsnet.api_key');
        $senderId = config('sms.smsnet.sender_id');
        $apiUrl = config('sms.smsnet.api_url', 'https://api.smsnet.com.bd/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('SMS Net credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via SMS Net', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('SMS Net failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Brand SMS Gateway
     */
    protected function sendViaBrandSms(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.brandsms.api_key');
        $senderId = config('sms.brandsms.sender_id');
        $apiUrl = config('sms.brandsms.api_url', 'https://api.brandsms.com.bd/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Brand SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Brand SMS', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Brand SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Metrotel SMS Gateway
     */
    protected function sendViaMetrotel(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.metrotel.api_key');
        $senderId = config('sms.metrotel.sender_id');
        $apiUrl = config('sms.metrotel.api_url', 'https://api.metrotel.com.bd/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Metrotel SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Metrotel', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Metrotel SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Dianahost SMS Gateway
     */
    protected function sendViaDianahost(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.dianahost.api_key');
        $senderId = config('sms.dianahost.sender_id');
        $apiUrl = config('sms.dianahost.api_url', 'https://sms.dianahost.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Dianahost SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Dianahost', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Dianahost SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via SMS in BD Gateway
     */
    protected function sendViaSmsInBd(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.smsinbd.api_key');
        $senderId = config('sms.smsinbd.sender_id');
        $apiUrl = config('sms.smsinbd.api_url', 'https://api.smsinbd.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('SMS in BD credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via SMS in BD', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('SMS in BD failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send via Dhakasoft BD SMS Gateway
     */
    protected function sendViaDhakasoftBd(string $phoneNumber, string $message): bool
    {
        $apiKey = config('sms.dhakasoftbd.api_key');
        $senderId = config('sms.dhakasoftbd.sender_id');
        $apiUrl = config('sms.dhakasoftbd.api_url', 'https://api.dhakasoftbd.com/smsapi');

        if (! $apiKey || ! $senderId) {
            Log::warning('Dhakasoft BD SMS credentials not configured');

            return false;
        }

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'type' => 'text',
            'contacts' => $phoneNumber,
            'senderid' => $senderId,
            'msg' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via Dhakasoft BD', ['phone' => $phoneNumber]);

            return true;
        }

        Log::error('Dhakasoft BD SMS failed', ['phone' => $phoneNumber, 'response' => $response->body()]);

        return false;
    }

    /**
     * Send test SMS using a specific gateway
     *
     * @return array{success: bool, log: SmsLog} Result with success status and SMS log
     */
    public function sendTestSms(SmsGateway $gateway, string $phoneNumber): array
    {
        $testMessage = "Test SMS from {$gateway->name}. This is a test message to verify gateway configuration. Sent at " . now()->format('Y-m-d H:i:s');
        $smsLog = null;

        try {
            // Create SMS log for test
            $smsLog = $this->logSms(
                $phoneNumber,
                $testMessage,
                SmsLog::STATUS_PENDING,
                $gateway->id,
                auth()->id(),
                null,
                $gateway->tenant_id
            );

            // Determine which gateway method to use based on provider
            $result = match ($gateway->provider) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $testMessage),
                'nexmo', 'vonage' => $this->sendViaNexmo($phoneNumber, $testMessage),
                'bulksms' => $this->sendViaBulkSms($phoneNumber, $testMessage),
                'bangladeshi' => $this->sendViaBangladeshiGateway($phoneNumber, $testMessage),
                'maestro' => $this->sendViaMaestro($phoneNumber, $testMessage),
                'robi' => $this->sendViaRobi($phoneNumber, $testMessage),
                'm2mbd' => $this->sendViaM2mbd($phoneNumber, $testMessage),
                'bangladeshsms' => $this->sendViaBangladeshSms($phoneNumber, $testMessage),
                'bulksmsbd' => $this->sendViaBulkSmsBd($phoneNumber, $testMessage),
                'btssms' => $this->sendViaBtsSms($phoneNumber, $testMessage),
                '880sms' => $this->sendVia880Sms($phoneNumber, $testMessage),
                'bdsmartpay' => $this->sendViaBdSmartPay($phoneNumber, $testMessage),
                'elitbuzz' => $this->sendViaElitbuzz($phoneNumber, $testMessage),
                'sslwireless' => $this->sendViaSslWireless($phoneNumber, $testMessage),
                'adnsms' => $this->sendViaAdnSms($phoneNumber, $testMessage),
                '24smsbd' => $this->sendVia24SmsBd($phoneNumber, $testMessage),
                'smsnet' => $this->sendViaSmsNet($phoneNumber, $testMessage),
                'brandsms' => $this->sendViaBrandSms($phoneNumber, $testMessage),
                'metrotel' => $this->sendViaMetrotel($phoneNumber, $testMessage),
                'dianahost' => $this->sendViaDianahost($phoneNumber, $testMessage),
                'smsinbd' => $this->sendViaSmsInBd($phoneNumber, $testMessage),
                'dhakasoftbd' => $this->sendViaDhakasoftBd($phoneNumber, $testMessage),
                default => throw new \Exception("Unsupported SMS provider: {$gateway->provider}"),
            };

            // Update log status
            if ($result) {
                $smsLog->markAsSent();
                Log::info('Test SMS sent successfully', [
                    'gateway' => $gateway->name,
                    'provider' => $gateway->provider,
                    'phone' => $phoneNumber,
                    'log_id' => $smsLog->id,
                ]);
            } else {
                $smsLog->markAsFailed('Gateway returned false - check gateway configuration');
                Log::warning('Test SMS failed', [
                    'gateway' => $gateway->name,
                    'provider' => $gateway->provider,
                    'phone' => $phoneNumber,
                    'log_id' => $smsLog->id,
                ]);
            }

            return ['success' => $result, 'log' => $smsLog];
        } catch (\Exception $e) {
            // Mark log as failed if it was created
            if ($smsLog) {
                $smsLog->markAsFailed($e->getMessage());
            }

            Log::error('Test SMS sending failed', [
                'gateway' => $gateway->name,
                'provider' => $gateway->provider,
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'log_id' => $smsLog?->id,
            ]);

            // Return the log even if failed, so controller can display it
            return ['success' => false, 'log' => $smsLog];
        }
    }
}
