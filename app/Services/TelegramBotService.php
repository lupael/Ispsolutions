<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    private string $botToken;

    private string $apiUrl;

    private bool $enabled;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->enabled = config('services.telegram.enabled', false);
    }

    /**
     * Send a text message
     */
    public function sendMessage(string $chatId, string $text, array $options = []): array
    {
        if (! $this->enabled) {
            Log::info('Telegram bot service is disabled');

            return ['success' => false, 'error' => 'Telegram bot service is disabled'];
        }

        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post("{$this->apiUrl}/sendMessage", $params);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message_id' => $response->json('result.message_id'),
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('result.message_id'),
                    'data' => $response->json('result'),
                ];
            }

            Log::error('Telegram API error', [
                'chat_id' => $chatId,
                'error' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('description', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Telegram service exception', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a message with inline keyboard
     */
    public function sendMessageWithKeyboard(string $chatId, string $text, array $buttons): array
    {
        $keyboard = [
            'inline_keyboard' => $buttons,
        ];

        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    /**
     * Send invoice notification
     */
    public function sendInvoiceNotification(string $chatId, array $invoiceData): array
    {
        $invoiceNumber = $invoiceData['invoice_number'] ?? 'N/A';
        $amount = $invoiceData['amount'] ?? '0.00';
        $dueDate = $invoiceData['due_date'] ?? 'N/A';
        $status = $invoiceData['status'] ?? 'Pending';

        $text = "<b>📄 New Invoice</b>\n\n"
            . "Invoice #: <code>{$invoiceNumber}</code>\n"
            . "Amount: <b>\${$amount}</b>\n"
            . "Due Date: {$dueDate}\n"
            . "Status: {$status}\n\n"
            . 'Please pay your invoice on time to avoid service interruption.';

        $buttons = [[
            [
                'text' => '💳 Pay Now',
                'url' => $invoiceData['payment_url'] ?? '#',
            ],
            [
                'text' => '📥 Download PDF',
                'url' => $invoiceData['pdf_url'] ?? '#',
            ],
        ]];

        return $this->sendMessageWithKeyboard($chatId, $text, $buttons);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(string $chatId, array $paymentData): array
    {
        $amount = $paymentData['amount'] ?? '0.00';
        $date = $paymentData['date'] ?? date('Y-m-d');
        $receiptNumber = $paymentData['receipt_number'] ?? 'N/A';
        $method = $paymentData['method'] ?? 'N/A';

        $text = "<b>✅ Payment Received</b>\n\n"
            . "Amount: <b>\${$amount}</b>\n"
            . "Date: {$date}\n"
            . "Receipt: <code>{$receiptNumber}</code>\n"
            . "Method: {$method}\n\n"
            . 'Thank you for your payment!';

        $buttons = [[
            [
                'text' => '📥 Download Receipt',
                'url' => $paymentData['receipt_url'] ?? '#',
            ],
        ]];

        return $this->sendMessageWithKeyboard($chatId, $text, $buttons);
    }

    /**
     * Send service expiration warning
     */
    public function sendExpirationWarning(string $chatId, array $serviceData): array
    {
        $text = "<b>⚠️ Service Expiration Warning</b>\n\n"
            . "Your service will expire in <b>{$serviceData['days_remaining']} days</b>.\n"
            . "Package: {$serviceData['package_name']}\n"
            . "Expiry Date: {$serviceData['expiry_date']}\n\n"
            . 'Please renew to avoid service interruption.';

        $buttons = [[
            [
                'text' => '🔄 Renew Now',
                'url' => $serviceData['renew_url'] ?? '#',
            ],
        ]];

        return $this->sendMessageWithKeyboard($chatId, $text, $buttons);
    }

    /**
     * Send account status notification
     */
    public function sendAccountStatusNotification(string $chatId, array $statusData): array
    {
        $emoji = $statusData['is_active'] ? '✅' : '🔒';
        $statusText = $statusData['is_active'] ? 'Active' : 'Locked';

        $text = "<b>{$emoji} Account Status Update</b>\n\n"
            . "Status: <b>{$statusText}</b>\n"
            . "Username: <code>{$statusData['username']}</code>\n"
            . "Package: {$statusData['package_name']}\n";

        if (! $statusData['is_active']) {
            $text .= "Reason: {$statusData['reason']}\n\n";
            $text .= 'Please contact support or make payment to unlock your account.';
        } else {
            $text .= "\nYour account is now active. Enjoy our services!";
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send network status notification
     */
    public function sendNetworkStatusNotification(string $chatId, array $networkData): array
    {
        $status = $networkData['is_online'] ? '🟢 Online' : '🔴 Offline';

        $text = "<b>🌐 Network Status</b>\n\n"
            . "Status: {$status}\n"
            . "IP Address: <code>{$networkData['ip_address']}</code>\n"
            . "Download: {$networkData['download_speed']}\n"
            . "Upload: {$networkData['upload_speed']}\n"
            . "Session Time: {$networkData['session_time']}";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send bandwidth usage alert
     */
    public function sendBandwidthAlert(string $chatId, array $usageData): array
    {
        $text = "<b>📊 Bandwidth Usage Alert</b>\n\n"
            . "Used: <b>{$usageData['used_gb']} GB</b>\n"
            . "Limit: {$usageData['limit_gb']} GB\n"
            . "Percentage: {$usageData['percentage']}%\n\n";

        if ($usageData['percentage'] >= 90) {
            $text .= "⚠️ You have used {$usageData['percentage']}% of your bandwidth limit!";
        } else {
            $text .= 'You are approaching your bandwidth limit.';
        }

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Send maintenance notification
     */
    public function sendMaintenanceNotification(string $chatId, array $maintenanceData): array
    {
        $text = "<b>🔧 Scheduled Maintenance</b>\n\n"
            . "Date: {$maintenanceData['date']}\n"
            . "Time: {$maintenanceData['start_time']} - {$maintenanceData['end_time']}\n"
            . "Duration: {$maintenanceData['duration']}\n\n"
            . "Affected Services: {$maintenanceData['affected_services']}\n\n"
            . 'We apologize for any inconvenience.';

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Handle webhook updates
     */
    public function handleWebhook(array $update): array
    {
        Log::info('Telegram webhook received', ['update' => $update]);

        if (! isset($update['message'])) {
            return ['success' => false, 'error' => 'No message in update'];
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Handle commands
        if (str_starts_with($text, '/')) {
            return $this->handleCommand($chatId, $text);
        }

        return ['success' => true, 'message' => 'Webhook processed'];
    }

    /**
     * Handle bot commands
     */
    private function handleCommand(string $chatId, string $command): array
    {
        return match (trim($command)) {
            '/start' => $this->sendWelcomeMessage($chatId),
            '/help' => $this->sendHelpMessage($chatId),
            '/status' => $this->sendMessage($chatId, 'To check your account status, please visit the customer portal.'),
            '/balance' => $this->sendMessage($chatId, 'To check your balance, please visit the customer portal.'),
            default => $this->sendMessage($chatId, 'Unknown command. Type /help for available commands.'),
        };
    }

    /**
     * Send welcome message
     */
    private function sendWelcomeMessage(string $chatId): array
    {
        $text = "<b>👋 Welcome to ISP Solution Bot!</b>\n\n"
            . "I can help you with:\n"
            . "• Invoice notifications\n"
            . "• Payment confirmations\n"
            . "• Service status updates\n"
            . "• Network alerts\n\n"
            . 'Type /help to see available commands.';

        $buttons = [[
            [
                'text' => '🌐 Visit Portal',
                'url' => config('app.url'),
            ],
        ]];

        return $this->sendMessageWithKeyboard($chatId, $text, $buttons);
    }

    /**
     * Send help message
     */
    private function sendHelpMessage(string $chatId): array
    {
        $text = "<b>📖 Available Commands</b>\n\n"
            . "/start - Welcome message\n"
            . "/help - Show this help\n"
            . "/status - Check account status\n"
            . "/balance - Check account balance\n\n"
            . 'For more features, please visit our customer portal.';

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Set webhook
     */
    public function setWebhook(string $url): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $url,
            ]);

            return [
                'success' => $response->json('ok', false),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = Http::get("{$this->apiUrl}/getWebhookInfo");

            return [
                'success' => $response->json('ok', false),
                'data' => $response->json('result'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->botToken);
    }
}
