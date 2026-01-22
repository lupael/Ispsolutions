<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;

    private string $accessToken;

    private string $phoneNumberId;

    private bool $enabled;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->accessToken = config('services.whatsapp.access_token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->enabled = config('services.whatsapp.enabled', false);
    }

    /**
     * Send a text message via WhatsApp Business API
     */
    public function sendTextMessage(string $to, string $message): array
    {
        if (! $this->enabled) {
            Log::info('WhatsApp service is disabled');

            return ['success' => false, 'error' => 'WhatsApp service is disabled'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->formatPhoneNumber($to),
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $to,
                    'message_id' => $response->json('messages.0.id'),
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                ];
            }

            Log::error('WhatsApp API error', [
                'to' => $to,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a template message via WhatsApp Business API
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        if (! $this->enabled) {
            return ['success' => false, 'error' => 'WhatsApp service is disabled'];
        }

        try {
            $components = [];
            if (! empty($parameters)) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => collect($parameters)->map(fn ($param) => [
                        'type' => 'text',
                        'text' => $param,
                    ])->toArray(),
                ];
            }

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->formatPhoneNumber($to),
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => [
                            'code' => 'en',
                        ],
                        'components' => $components,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp template message sent', [
                    'to' => $to,
                    'template' => $templateName,
                    'message_id' => $response->json('messages.0.id'),
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp template service exception', [
                'to' => $to,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send invoice notification
     */
    public function sendInvoiceNotification(string $to, array $invoiceData): array
    {
        $invoiceNumber = $invoiceData['invoice_number'] ?? 'N/A';
        $amount = $invoiceData['amount'] ?? '0.00';
        $dueDate = $invoiceData['due_date'] ?? 'N/A';
        $status = $invoiceData['status'] ?? 'Pending';

        $message = "Invoice #{$invoiceNumber}\n\n"
            . "Amount: \${$amount}\n"
            . "Due Date: {$dueDate}\n"
            . "Status: {$status}\n\n"
            . 'Please pay your invoice on time to avoid service interruption.';

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(string $to, array $paymentData): array
    {
        $amount = $paymentData['amount'] ?? '0.00';
        $date = $paymentData['date'] ?? date('Y-m-d');
        $receiptNumber = $paymentData['receipt_number'] ?? 'N/A';

        $message = "Payment Received ✓\n\n"
            . "Amount: \${$amount}\n"
            . "Date: {$date}\n"
            . "Receipt: {$receiptNumber}\n\n"
            . 'Thank you for your payment!';

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Send service expiration warning
     */
    public function sendExpirationWarning(string $to, array $serviceData): array
    {
        $daysRemaining = $serviceData['days_remaining'] ?? 0;
        $packageName = $serviceData['package_name'] ?? 'N/A';
        $expiryDate = $serviceData['expiry_date'] ?? 'N/A';

        $message = "⚠️ Service Expiration Warning\n\n"
            . "Your service will expire in {$daysRemaining} days.\n"
            . "Package: {$packageName}\n"
            . "Expiry Date: {$expiryDate}\n\n"
            . 'Please renew to avoid service interruption.';

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Send account locked notification
     */
    public function sendAccountLockedNotification(string $to, string $reason): array
    {
        $message = "🔒 Account Locked\n\n"
            . "Your account has been locked due to: {$reason}\n\n"
            . 'Please contact support or make payment to unlock your account.';

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Format phone number to E.164 format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Get default country code from config
        $defaultCountryCode = config('services.whatsapp.default_country_code', '880');

        // Add country code if not present
        if (! str_starts_with($phone, $defaultCountryCode) && ! str_starts_with($phone, '+' . $defaultCountryCode)) {
            // Remove leading zero if present
            $phone = ltrim($phone, '0');
            $phone = $defaultCountryCode . $phone;
        }

        // Basic validation: ensure non-empty and reasonable E.164-like length
        // E.164 numbers are up to 15 digits; require a minimal length to avoid obviously invalid numbers
        $length = strlen($phone);
        if ($length === 0 || $length < 8 || $length > 15) {
            Log::warning('WhatsAppService: formatted phone number is invalid', [
                'original' => func_get_arg(0),
                'formatted' => $phone,
                'length' => $length,
            ]);

            return '';
        }

        return $phone;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $appSecret = config('services.whatsapp.app_secret', '');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Check if service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->accessToken) && ! empty($this->phoneNumberId);
    }
}
