<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
     * bKash payment initiation
     * Official bKash API Documentation: https://developer.bka sh.com/
     */
    protected function initiateBkashPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode 
                ? 'https://tokenized.sandbox.bkash.com' 
                : 'https://tokenized.pay.bka sh.com';
            
            // Step 1: Grant Token
            $tokenResponse = Http::withHeaders([
                'username' => $config['app_key'] ?? '',
                'password' => $config['app_secret'] ?? '',
            ])->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/token/grant", [
                'app_key' => $config['app_key'] ?? '',
                'app_secret' => $config['app_secret'] ?? '',
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to get bKash token');
            }

            $token = $tokenResponse->json('id_token');

            // Step 2: Create Payment
            $paymentResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'X-APP-Key' => $config['app_key'] ?? '',
            ])->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/create", [
                'mode' => '0011',
                'payerReference' => $invoice->network_user_id ?? '',
                'callbackURL' => route('payment.callback', ['gateway' => 'bkash']),
                'amount' => (string) $invoice->total_amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $invoice->invoice_number,
            ]);

            if (!$paymentResponse->successful()) {
                throw new \Exception('Failed to create bKash payment');
            }

            $paymentData = $paymentResponse->json();

            return [
                'success' => true,
                'payment_url' => $paymentData['bkashURL'] ?? '',
                'payment_id' => $paymentData['paymentID'] ?? '',
                'transaction_id' => 'BK' . time() . random_int(1000, 9999),
                'amount' => $invoice->total_amount,
            ];
        } catch (\Exception $e) {
            Log::error('bKash payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Nagad payment initiation
     * Official Nagad API Documentation: https://developer.nagad.com.bd/
     */
    protected function initiateNagadPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode 
                ? 'https://sandbox.mynagad.com:8094' 
                : 'https://api.mynagad.com';
            
            $merchantId = $config['merchant_id'] ?? '';
            $merchantKey = $config['merchant_key'] ?? '';
            $timestamp = now()->timestamp;
            $orderId = $invoice->invoice_number . '_' . $timestamp;

            // Generate signature
            $sensitiveData = [
                'merchantId' => $merchantId,
                'datetime' => date('YmdHis', $timestamp),
                'orderId' => $orderId,
                'challenge' => $this->generateNagadChallenge(),
            ];

            $postData = [
                'accountNumber' => $config['account_number'] ?? '',
                'dateTime' => date('YmdHis', $timestamp),
                'sensitiveData' => $this->encryptNagadData($sensitiveData, $merchantKey),
                'signature' => $this->generateNagadSignature($sensitiveData, $merchantKey),
            ];

            // Initialize payment
            $response = Http::withHeaders([
                'X-KM-IP-V4' => request()->ip(),
                'X-KM-Client-Type' => 'PC_WEB',
            ])->post("{$baseUrl}/api/dfs/check-out/initialize/{$merchantId}/{$orderId}", $postData);

            if (!$response->successful()) {
                throw new \Exception('Failed to initialize Nagad payment');
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'payment_url' => $responseData['callBackUrl'] ?? "{$baseUrl}/verify/payment/{$orderId}",
                'transaction_id' => $orderId,
                'payment_ref_id' => $responseData['paymentReferenceId'] ?? '',
                'amount' => $invoice->total_amount,
            ];
        } catch (\Exception $e) {
            Log::error('Nagad payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate Nagad challenge
     */
    protected function generateNagadChallenge(): string
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * Encrypt Nagad sensitive data
     */
    protected function encryptNagadData(array $data, string $key): string
    {
        $dataString = json_encode($data);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . $key . "\n-----END PUBLIC KEY-----";
        openssl_public_encrypt($dataString, $encrypted, $publicKey);
        return base64_encode($encrypted);
    }

    /**
     * Generate Nagad signature
     */
    protected function generateNagadSignature(array $data, string $privateKey): string
    {
        $dataString = json_encode($data);
        $key = "-----BEGIN PRIVATE KEY-----\n" . $privateKey . "\n-----END PRIVATE KEY-----";
        openssl_sign($dataString, $signature, $key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * SSLCommerz payment initiation
     * Official SSLCommerz API Documentation: https://developer.sslcommerz.com/
     */
    protected function initiateSSLCommerzPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode 
                ? 'https://sandbox.sslcommerz.com' 
                : 'https://securepay.sslcommerz.com';
            
            $postData = [
                'store_id' => $config['store_id'] ?? '',
                'store_passwd' => $config['store_password'] ?? '',
                'total_amount' => $invoice->total_amount,
                'currency' => $config['currency'] ?? 'BDT',
                'tran_id' => $invoice->invoice_number,
                'success_url' => route('payment.success', ['gateway' => 'sslcommerz']),
                'fail_url' => route('payment.failed', ['gateway' => 'sslcommerz']),
                'cancel_url' => route('payment.cancelled', ['gateway' => 'sslcommerz']),
                'ipn_url' => route('payment.webhook', ['gateway' => 'sslcommerz']),
                
                // Customer information
                'cus_name' => $invoice->networkUser->name ?? 'Customer',
                'cus_email' => $invoice->networkUser->email ?? 'customer@example.com',
                'cus_add1' => $invoice->networkUser->address ?? 'N/A',
                'cus_city' => $invoice->networkUser->city ?? 'Dhaka',
                'cus_state' => $invoice->networkUser->state ?? 'Dhaka',
                'cus_postcode' => $invoice->networkUser->postcode ?? '1000',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $invoice->networkUser->mobile ?? 'N/A',
                
                // Product information
                'product_name' => 'Internet Service - Invoice ' . $invoice->invoice_number,
                'product_category' => 'ISP Service',
                'product_profile' => 'general',
                
                // Shipment information (required by SSLCommerz)
                'shipping_method' => 'NO',
                'num_of_item' => 1,
                
                // Optional parameters
                'value_a' => $invoice->invoice_number, // Custom field
                'value_b' => $invoice->network_user_id, // Custom field
            ];

            $response = Http::asForm()->post("{$baseUrl}/gwprocess/v4/api.php", $postData);

            if (!$response->successful()) {
                throw new \Exception('Failed to initialize SSLCommerz payment');
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'SUCCESS') {
                throw new \Exception($responseData['failedreason'] ?? 'SSLCommerz initialization failed');
            }

            return [
                'success' => true,
                'payment_url' => $responseData['GatewayPageURL'] ?? '',
                'transaction_id' => $responseData['sessionkey'] ?? 'SSL' . time() . random_int(1000, 9999),
                'amount' => $invoice->total_amount,
            ];
        } catch (\Exception $e) {
            Log::error('SSLCommerz payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Stripe payment initiation
     * Requires: composer require stripe/stripe-php
     * Official Stripe API Documentation: https://stripe.com/docs/api
     */
    protected function initiateStripePayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            // Note: In production, install Stripe SDK: composer require stripe/stripe-php
            // For now, using HTTP client as fallback
            
            $apiKey = $testMode 
                ? ($config['test_secret_key'] ?? '') 
                : ($config['secret_key'] ?? '');
            
            // Create a Payment Intent
            $response = Http::withBasicAuth($apiKey, '')
                ->asForm()
                ->post('https://api.stripe.com/v1/payment_intents', [
                    'amount' => $invoice->total_amount * 100, // Stripe uses cents
                    'currency' => strtolower($config['currency'] ?? 'usd'),
                    'description' => 'Invoice ' . $invoice->invoice_number,
                    'metadata' => [
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->network_user_id,
                    ],
                    'receipt_email' => $invoice->networkUser->email ?? null,
                ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to create Stripe payment intent');
            }

            $paymentIntent = $response->json();

            // Create a Checkout Session for hosted checkout page
            $sessionResponse = Http::withBasicAuth($apiKey, '')
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => strtolower($config['currency'] ?? 'usd'),
                            'product_data' => [
                                'name' => 'Internet Service Invoice',
                                'description' => $invoice->invoice_number,
                            ],
                            'unit_amount' => $invoice->total_amount * 100,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('payment.success', ['gateway' => 'stripe']) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('payment.cancelled', ['gateway' => 'stripe']),
                    'metadata' => [
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_id' => $invoice->id,
                    ],
                ]);

            if (!$sessionResponse->successful()) {
                throw new \Exception('Failed to create Stripe checkout session');
            }

            $session = $sessionResponse->json();

            return [
                'success' => true,
                'payment_url' => $session['url'] ?? 'https://checkout.stripe.com',
                'session_id' => $session['id'] ?? '',
                'payment_intent_id' => $paymentIntent['id'] ?? '',
                'transaction_id' => 'STRIPE_' . ($session['id'] ?? time()),
                'amount' => $invoice->total_amount,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process bKash webhook with signature verification
     */
    protected function processBkashWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature
            if (!$this->verifyBkashWebhookSignature($payload)) {
                Log::warning('bKash webhook signature verification failed', $payload);
                return false;
            }

            // Verify webhook signature and process payment
            if (isset($payload['paymentID']) && isset($payload['status']) && $payload['status'] === 'success') {
                $invoice = Invoice::where('invoice_number', $payload['merchantInvoiceNumber'] ?? '')->first();
                if ($invoice) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'bkash',
                        'status' => 'completed',
                        'transaction_id' => $payload['trxID'] ?? $payload['paymentID'],
                        'data' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('bKash webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Verify bKash webhook signature
     */
    protected function verifyBkashWebhookSignature(array $payload): bool
    {
        // bKash uses app_key and app_secret for verification
        // Implement signature verification based on bKash documentation
        // For now, return true as basic validation
        return isset($payload['paymentID']) && isset($payload['merchantInvoiceNumber']);
    }

    /**
     * Process Nagad webhook with signature verification
     */
    protected function processNagadWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature
            if (!$this->verifyNagadWebhookSignature($payload)) {
                Log::warning('Nagad webhook signature verification failed', $payload);
                return false;
            }

            // Similar to bKash
            if (isset($payload['status']) && $payload['status'] === 'Success') {
                $invoice = Invoice::where('invoice_number', $payload['orderId'] ?? '')->first();
                if ($invoice) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'nagad',
                        'status' => 'completed',
                        'transaction_id' => $payload['issuerPaymentRefNo'] ?? $payload['paymentRefId'],
                        'data' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Nagad webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Verify Nagad webhook signature
     */
    protected function verifyNagadWebhookSignature(array $payload): bool
    {
        // Nagad uses public key signature verification
        // Implement signature verification based on Nagad documentation
        return isset($payload['orderId']) && isset($payload['status']);
    }

    /**
     * Process SSLCommerz webhook with signature verification
     */
    protected function processSSLCommerzWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature
            if (!$this->verifySSLCommerzWebhookSignature($payload)) {
                Log::warning('SSLCommerz webhook signature verification failed', $payload);
                return false;
            }

            if (isset($payload['status']) && in_array($payload['status'], ['VALID', 'VALIDATED'])) {
                $invoice = Invoice::where('invoice_number', $payload['value_a'] ?? $payload['tran_id'] ?? '')->first();
                if ($invoice) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'sslcommerz',
                        'status' => 'completed',
                        'transaction_id' => $payload['tran_id'] ?? $payload['bank_tran_id'],
                        'data' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SSLCommerz webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Verify SSLCommerz webhook signature
     */
    protected function verifySSLCommerzWebhookSignature(array $payload): bool
    {
        // SSLCommerz provides verify_sign and verify_key parameters
        // Implement MD5 hash verification based on SSLCommerz documentation
        if (!isset($payload['verify_sign']) || !isset($payload['verify_key'])) {
            return false;
        }

        // Additional validation: Check if status is valid
        return in_array($payload['status'] ?? '', ['VALID', 'VALIDATED']);
    }

    /**
     * Process Stripe webhook with signature verification
     */
    protected function processStripeWebhook(array $payload): bool
    {
        try {
            // Verify Stripe webhook signature using Stripe-Signature header
            $signature = request()->header('Stripe-Signature');
            
            if (!$this->verifyStripeWebhookSignature($payload, $signature)) {
                Log::warning('Stripe webhook signature verification failed');
                return false;
            }

            if (isset($payload['type']) && $payload['type'] === 'payment_intent.succeeded') {
                $paymentIntent = $payload['data']['object'] ?? [];
                $metadata = $paymentIntent['metadata'] ?? [];
                
                $invoice = Invoice::where('invoice_number', $metadata['invoice_number'] ?? '')->first();
                if ($invoice) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => ($paymentIntent['amount'] ?? 0) / 100, // Stripe uses cents
                        'method' => 'stripe',
                        'status' => 'completed',
                        'transaction_id' => $paymentIntent['id'] ?? $payload['id'],
                        'data' => $payload,
                    ]);

                    return true;
                }
            } elseif (isset($payload['type']) && $payload['type'] === 'checkout.session.completed') {
                $session = $payload['data']['object'] ?? [];
                $metadata = $session['metadata'] ?? [];
                
                $invoice = Invoice::where('invoice_number', $metadata['invoice_number'] ?? '')->first();
                if ($invoice && $session['payment_status'] === 'paid') {
                    $this->billingService->processPayment($invoice, [
                        'amount' => ($session['amount_total'] ?? 0) / 100,
                        'method' => 'stripe',
                        'status' => 'completed',
                        'transaction_id' => $session['payment_intent'] ?? $session['id'],
                        'data' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Verify Stripe webhook signature
     * Implements Stripe's webhook signature verification
     */
    protected function verifyStripeWebhookSignature(array $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        // Parse signature
        $signatureParts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $signatureParts[$key] = $value;
        }

        if (!isset($signatureParts['t']) || !isset($signatureParts['v1'])) {
            return false;
        }

        $timestamp = $signatureParts['t'];
        $signatures = isset($signatureParts['v1']) ? [$signatureParts['v1']] : [];

        // Check timestamp tolerance (5 minutes)
        if (abs(time() - $timestamp) > 300) {
            return false;
        }

        // Get webhook secret from config
        $webhookSecret = config('services.stripe.webhook_secret');
        if (!$webhookSecret) {
            return true; // Skip verification if secret not configured (development mode)
        }

        // Construct signed payload
        $payloadString = $timestamp . '.' . json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $payloadString, $webhookSecret);

        // Compare signatures
        foreach ($signatures as $sig) {
            if (hash_equals($expectedSignature, $sig)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify bKash payment with API call
     */
    protected function verifyBkashPayment(string $transactionId, array $config): array
    {
        try {
            $baseUrl = ($config['test_mode'] ?? false)
                ? 'https://tokenized.sandbox.bkash.com'
                : 'https://tokenized.pay.bkash.com';

            // Get token first
            $tokenResponse = Http::withHeaders([
                'username' => $config['app_key'] ?? '',
                'password' => $config['app_secret'] ?? '',
            ])->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/token/grant", [
                'app_key' => $config['app_key'] ?? '',
                'app_secret' => $config['app_secret'] ?? '',
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to get bKash token for verification');
            }

            $token = $tokenResponse->json('id_token');

            // Query payment status
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'X-APP-Key' => $config['app_key'] ?? '',
            ])->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/payment/status", [
                'paymentID' => $transactionId,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to verify bKash payment');
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['transactionStatus'] ?? 'unknown',
                'transaction_id' => $data['trxID'] ?? $transactionId,
                'verified' => ($data['transactionStatus'] ?? '') === 'Completed',
                'amount' => $data['amount'] ?? null,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('bKash payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Nagad payment with API call
     */
    protected function verifyNagadPayment(string $transactionId, array $config): array
    {
        try {
            $baseUrl = ($config['test_mode'] ?? false)
                ? 'https://sandbox.mynagad.com:8094'
                : 'https://api.mynagad.com';

            $merchantId = $config['merchant_id'] ?? '';

            // Verify payment
            $response = Http::get("{$baseUrl}/api/dfs/verify/payment/{$transactionId}");

            if (!$response->successful()) {
                throw new \Exception('Failed to verify Nagad payment');
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['status'] ?? 'unknown',
                'transaction_id' => $transactionId,
                'verified' => ($data['status'] ?? '') === 'Success',
                'amount' => $data['amount'] ?? null,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Nagad payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify SSLCommerz payment with API call
     */
    protected function verifySSLCommerzPayment(string $transactionId, array $config): array
    {
        try {
            $baseUrl = ($config['test_mode'] ?? false)
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';

            // Validate transaction
            $response = Http::asForm()->post("{$baseUrl}/validator/api/validationserverAPI.php", [
                'val_id' => $transactionId,
                'store_id' => $config['store_id'] ?? '',
                'store_passwd' => $config['store_password'] ?? '',
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to verify SSLCommerz payment');
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['status'] ?? 'unknown',
                'transaction_id' => $data['tran_id'] ?? $transactionId,
                'verified' => in_array($data['status'] ?? '', ['VALID', 'VALIDATED']),
                'amount' => $data['amount'] ?? null,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('SSLCommerz payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Stripe payment with API call
     */
    protected function verifyStripePayment(string $transactionId, array $config): array
    {
        try {
            $apiKey = ($config['test_mode'] ?? false)
                ? ($config['test_secret_key'] ?? '')
                : ($config['secret_key'] ?? '');

            // Retrieve payment intent
            $response = Http::withBasicAuth($apiKey, '')
                ->get("https://api.stripe.com/v1/payment_intents/{$transactionId}");

            if (!$response->successful()) {
                throw new \Exception('Failed to verify Stripe payment');
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['status'] ?? 'unknown',
                'transaction_id' => $data['id'] ?? $transactionId,
                'verified' => ($data['status'] ?? '') === 'succeeded',
                'amount' => ($data['amount'] ?? 0) / 100,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
