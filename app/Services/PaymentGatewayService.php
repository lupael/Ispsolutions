<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Initiate payment through gateway
     */
    public function initiatePayment(Invoice $invoice, string $gatewaySlug, array $additionalData = []): array
    {
        $gateway = PaymentGateway::where('slug', $gatewaySlug)
            ->where('tenant_id', $invoice->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();

        $config = $gateway->configuration;

        return match ($gatewaySlug) {
            'bkash' => $this->initiateBkashPayment($invoice, $config, $gateway->test_mode),
            'nagad' => $this->initiateNagadPayment($invoice, $config, $gateway->test_mode),
            'sslcommerz' => $this->initiateSSLCommerzPayment($invoice, $config, $gateway->test_mode),
            'stripe' => $this->initiateStripePayment($invoice, $config, $gateway->test_mode),
            default => throw new \Exception("Unsupported payment gateway: {$gatewaySlug}"),
        };
    }

    /**
     * Process webhook callback from payment gateway
     */
    public function processWebhook(string $gatewaySlug, array $payload): bool
    {
        Log::info("Processing webhook for {$gatewaySlug}", $payload);

        return match ($gatewaySlug) {
            'bkash' => $this->processBkashWebhook($payload),
            'nagad' => $this->processNagadWebhook($payload),
            'sslcommerz' => $this->processSSLCommerzWebhook($payload),
            'stripe' => $this->processStripeWebhook($payload),
            default => throw new \Exception("Unsupported payment gateway: {$gatewaySlug}"),
        };
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionId, string $gatewaySlug, int $tenantId): array
    {
        $gateway = PaymentGateway::where('slug', $gatewaySlug)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->firstOrFail();

        $config = $gateway->configuration;

        return match ($gatewaySlug) {
            'bkash' => $this->verifyBkashPayment($transactionId, $config),
            'nagad' => $this->verifyNagadPayment($transactionId, $config),
            'sslcommerz' => $this->verifySSLCommerzPayment($transactionId, $config),
            'stripe' => $this->verifyStripePayment($transactionId, $config),
            default => throw new \Exception("Unsupported payment gateway: {$gatewaySlug}"),
        };
    }

    /**
     * bKash payment initiation (stub)
     */
    protected function initiateBkashPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        // Implementation would make API call to bKash
        return [
            'payment_url' => $testMode ? 'https://sandbox.bkash.com/checkout' : 'https://bkash.com/checkout',
            'transaction_id' => 'BK' . time() . random_int(1000, 9999),
            'amount' => $invoice->total_amount,
        ];
    }

    /**
     * Nagad payment initiation (stub)
     */
    protected function initiateNagadPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        // Implementation would make API call to Nagad
        return [
            'payment_url' => $testMode ? 'https://sandbox.nagad.com/checkout' : 'https://nagad.com/checkout',
            'transaction_id' => 'NG' . time() . random_int(1000, 9999),
            'amount' => $invoice->total_amount,
        ];
    }

    /**
     * SSLCommerz payment initiation (stub)
     */
    protected function initiateSSLCommerzPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        // Implementation would make API call to SSLCommerz
        return [
            'payment_url' => $testMode ? 'https://sandbox.sslcommerz.com/checkout' : 'https://securepay.sslcommerz.com/checkout',
            'transaction_id' => 'SSL' . time() . random_int(1000, 9999),
            'amount' => $invoice->total_amount,
        ];
    }

    /**
     * Stripe payment initiation (stub)
     */
    protected function initiateStripePayment(Invoice $invoice, array $config, bool $testMode): array
    {
        // Implementation would use Stripe SDK
        return [
            'payment_url' => 'https://checkout.stripe.com',
            'transaction_id' => 'STRIPE' . time() . random_int(1000, 9999),
            'amount' => $invoice->total_amount,
        ];
    }

    /**
     * Process bKash webhook (stub)
     */
    protected function processBkashWebhook(array $payload): bool
    {
        // Verify webhook signature and process payment
        if (isset($payload['status']) && $payload['status'] === 'success') {
            $invoice = Invoice::where('invoice_number', $payload['invoice_number'] ?? '')->first();
            if ($invoice) {
                $this->billingService->processPayment($invoice, [
                    'amount' => $payload['amount'] ?? $invoice->total_amount,
                    'method' => 'bkash',
                    'status' => 'completed',
                    'transaction_id' => $payload['transaction_id'] ?? null,
                    'data' => $payload,
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Process Nagad webhook (stub)
     */
    protected function processNagadWebhook(array $payload): bool
    {
        // Similar to bKash
        if (isset($payload['status']) && $payload['status'] === 'success') {
            $invoice = Invoice::where('invoice_number', $payload['invoice_number'] ?? '')->first();
            if ($invoice) {
                $this->billingService->processPayment($invoice, [
                    'amount' => $payload['amount'] ?? $invoice->total_amount,
                    'method' => 'nagad',
                    'status' => 'completed',
                    'transaction_id' => $payload['transaction_id'] ?? null,
                    'data' => $payload,
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Process SSLCommerz webhook (stub)
     */
    protected function processSSLCommerzWebhook(array $payload): bool
    {
        if (isset($payload['status']) && $payload['status'] === 'VALID') {
            $invoice = Invoice::where('invoice_number', $payload['value_a'] ?? '')->first();
            if ($invoice) {
                $this->billingService->processPayment($invoice, [
                    'amount' => $payload['amount'] ?? $invoice->total_amount,
                    'method' => 'sslcommerz',
                    'status' => 'completed',
                    'transaction_id' => $payload['tran_id'] ?? null,
                    'data' => $payload,
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Process Stripe webhook (stub)
     */
    protected function processStripeWebhook(array $payload): bool
    {
        if (isset($payload['type']) && $payload['type'] === 'payment_intent.succeeded') {
            $invoice = Invoice::where('invoice_number', $payload['metadata']['invoice_number'] ?? '')->first();
            if ($invoice) {
                $this->billingService->processPayment($invoice, [
                    'amount' => ($payload['amount'] ?? 0) / 100, // Stripe uses cents
                    'method' => 'stripe',
                    'status' => 'completed',
                    'transaction_id' => $payload['id'] ?? null,
                    'data' => $payload,
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Verify bKash payment (stub)
     */
    protected function verifyBkashPayment(string $transactionId, array $config): array
    {
        // Would make API call to verify
        return [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'verified' => true,
        ];
    }

    /**
     * Verify Nagad payment (stub)
     */
    protected function verifyNagadPayment(string $transactionId, array $config): array
    {
        return [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'verified' => true,
        ];
    }

    /**
     * Verify SSLCommerz payment (stub)
     */
    protected function verifySSLCommerzPayment(string $transactionId, array $config): array
    {
        return [
            'status' => 'VALID',
            'transaction_id' => $transactionId,
            'verified' => true,
        ];
    }

    /**
     * Verify Stripe payment (stub)
     */
    protected function verifyStripePayment(string $transactionId, array $config): array
    {
        return [
            'status' => 'succeeded',
            'transaction_id' => $transactionId,
            'verified' => true,
        ];
    }
}
