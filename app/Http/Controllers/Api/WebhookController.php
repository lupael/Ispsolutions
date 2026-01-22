<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsappService,
        private TelegramBotService $telegramService
    ) {}

    /**
     * Handle WhatsApp webhook
     */
    public function whatsapp(Request $request): JsonResponse
    {
        // Webhook verification
        if ($request->has('hub_mode') && $request->has('hub_verify_token')) {
            $mode = $request->input('hub_mode');
            $token = $request->input('hub_verify_token');
            $challenge = $request->input('hub_challenge');

            if ($mode === 'subscribe' && $token === env('WHATSAPP_VERIFY_TOKEN')) {
                Log::info('WhatsApp webhook verified');

                return response($challenge, 200);
            }

            return response()->json(['error' => 'Invalid verification token'], 403);
        }

        // Verify signature
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();

        if ($signature && ! $this->whatsappService->verifyWebhookSignature($signature, $payload)) {
            Log::warning('WhatsApp webhook signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Process webhook
        $data = $request->all();
        Log::info('WhatsApp webhook received', ['data' => $data]);

        // Handle incoming messages
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'messages' && isset($change['value']['messages'])) {
                            foreach ($change['value']['messages'] as $message) {
                                $this->handleWhatsAppMessage($message);
                            }
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle Telegram webhook
     */
    public function telegram(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('Telegram webhook received', ['update' => $update]);

        $result = $this->telegramService->handleWebhook($update);

        return response()->json($result);
    }

    /**
     * Set up WhatsApp webhook
     */
    public function setupWhatsApp(): JsonResponse
    {
        // This is handled through Meta Business Suite
        return response()->json([
            'message' => 'WhatsApp webhook configuration is managed through Meta Business Suite',
            'verify_token' => config('services.whatsapp.verify_token'),
            'webhook_url' => route('api.webhook.whatsapp'),
        ]);
    }

    /**
     * Set up Telegram webhook
     */
    public function setupTelegram(): JsonResponse
    {
        $webhookUrl = route('api.webhook.telegram');
        $result = $this->telegramService->setWebhook($webhookUrl);

        return response()->json($result);
    }

    /**
     * Get Telegram webhook info
     */
    public function telegramWebhookInfo(): JsonResponse
    {
        $result = $this->telegramService->getWebhookInfo();

        return response()->json($result);
    }

    /**
     * Handle incoming WhatsApp message
     */
    private function handleWhatsAppMessage(array $message): void
    {
        $from = $message['from'] ?? null;
        $messageType = $message['type'] ?? null;
        $messageBody = $message['text']['body'] ?? null;

        if (! $from || ! $messageType) {
            return;
        }

        Log::info('Processing WhatsApp message', [
            'from' => $from,
            'type' => $messageType,
            'body' => $messageBody,
        ]);

        // Handle different message types
        if ($messageType === 'text' && $messageBody) {
            // Process text message
            $this->processTextMessage($from, $messageBody);
        }
    }

    /**
     * Process text message
     */
    private function processTextMessage(string $from, string $text): void
    {
        // Implement your custom logic here
        // For example, handle commands like "STATUS", "BALANCE", etc.

        $text = strtoupper(trim($text));

        switch ($text) {
            case 'STATUS':
                // Send account status
                $this->whatsappService->sendTextMessage($from, 'To check your account status, please visit our customer portal.');
                break;

            case 'BALANCE':
                // Send balance information
                $this->whatsappService->sendTextMessage($from, 'To check your balance, please visit our customer portal.');
                break;

            case 'HELP':
                // Send help message
                $helpMessage = "Available commands:\n"
                    . "STATUS - Check account status\n"
                    . "BALANCE - Check account balance\n"
                    . "HELP - Show this message\n\n"
                    . 'For more features, visit our customer portal.';
                $this->whatsappService->sendTextMessage($from, $helpMessage);
                break;

            default:
                $this->whatsappService->sendTextMessage($from, 'Unknown command. Reply with HELP to see available commands.');
                break;
        }
    }
}
