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
        try {
            $gateway = PaymentGateway::where('slug', $gatewaySlug)
                ->where('tenant_id', $invoice->tenant_id)
                ->where('is_active', true)
                ->firstOrFail();

            $config = $gateway->configuration;

            // Validate gateway configuration
            if (empty($config) || !is_array($config)) {
                throw new \Exception("Payment gateway '{$gatewaySlug}' is not properly configured.");
            }

            return match ($gatewaySlug) {
                'bkash' => $this->initiateBkashPayment($invoice, $config, $gateway->test_mode),
                'nagad' => $this->initiateNagadPayment($invoice, $config, $gateway->test_mode),
                'sslcommerz' => $this->initiateSSLCommerzPayment($invoice, $config, $gateway->test_mode),
                'stripe' => $this->initiateStripePayment($invoice, $config, $gateway->test_mode),
                default => throw new \Exception("Unsupported payment gateway: {$gatewaySlug}"),
            };
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'invoice_id' => $invoice->id,
                'gateway' => $gatewaySlug,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Process webhook callback from payment gateway
     */
    public function processWebhook(string $gatewaySlug, array $payload): bool
    {
        Log::info("Processing webhook for {$gatewaySlug}", [
            'gateway' => $gatewaySlug,
            'payload_keys' => array_keys($payload),
        ]);

        // Check for idempotency - prevent duplicate webhook processing
        $transactionId = $this->extractTransactionId($gatewaySlug, $payload);
        if ($transactionId && $this->isWebhookAlreadyProcessed($transactionId, $gatewaySlug)) {
            Log::warning('Duplicate webhook detected', [
                'gateway' => $gatewaySlug,
                'transaction_id' => $transactionId,
            ]);
            return true; // Return true to acknowledge receipt
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $result = match ($gatewaySlug) {
                'bkash' => $this->processBkashWebhook($payload),
                'nagad' => $this->processNagadWebhook($payload),
                'sslcommerz' => $this->processSSLCommerzWebhook($payload),
                'stripe' => $this->processStripeWebhook($payload),
                default => throw new \Exception("Unsupported payment gateway: {$gatewaySlug}"),
            };

            \Illuminate\Support\Facades\DB::commit();
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            Log::error('Webhook processing failed - transaction rolled back', [
                'gateway' => $gatewaySlug,
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Extract transaction ID from webhook payload
     */
    protected function extractTransactionId(string $gatewaySlug, array $payload): ?string
    {
        return match ($gatewaySlug) {
            'bkash' => $payload['trxID'] ?? null,
            'nagad' => $payload['orderId'] ?? null,
            'sslcommerz' => $payload['tran_id'] ?? null,
            'stripe' => $payload['id'] ?? null,
            default => null,
        };
    }

    /**
     * Check if webhook has already been processed
     */
    protected function isWebhookAlreadyProcessed(string $transactionId, string $gatewaySlug): bool
    {
        return Payment::where('transaction_id', $transactionId)
            ->where('gateway', $gatewaySlug)
            ->exists();
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
     * bKash payment initiation - Production Ready
     * Official bKash API Documentation: https://developer.bkash.com/
     */
    protected function initiateBkashPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode
                ? 'https://tokenized.sandbox.bkash.com'
                : 'https://tokenized.pay.bkash.com';

            $appKey = $config['app_key'] ?? '';
            $appSecret = $config['app_secret'] ?? '';
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';

            if (empty($appKey) || empty($appSecret) || empty($username) || empty($password)) {
                throw new \Exception('bKash credentials not configured');
            }

            // Step 1: Grant Token with proper headers
            $tokenResponse = Http::timeout(30)
                ->withHeaders([
                    'username' => $username,
                    'password' => $password,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/token/grant", [
                    'app_key' => $appKey,
                    'app_secret' => $appSecret,
                ]);

            if (! $tokenResponse->successful()) {
                $errorMsg = $tokenResponse->json('errorMessage') ?? 'Failed to get bKash token';
                Log::error('bKash token grant failed', [
                    'response' => $tokenResponse->json(),
                    'status' => $tokenResponse->status(),
                ]);
                throw new \Exception($errorMsg);
            }

            $token = $tokenResponse->json('id_token');

            if (empty($token)) {
                throw new \Exception('bKash token is empty');
            }

            // Step 2: Create Payment with retry logic
            $maxRetries = 2;
            $retryCount = 0;
            $paymentResponse = null;

            while ($retryCount <= $maxRetries) {
                $paymentResponse = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => "Bearer {$token}",
                        'X-APP-Key' => $appKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post("{$baseUrl}/v1.2.0-beta/tokenized/checkout/create", [
                        'mode' => '0011',
                        'payerReference' => (string) ($invoice->network_user_id ?? 'CUST-' . $invoice->id),
                        'callbackURL' => route('webhooks.payment', ['gateway' => 'bkash']),
                        'amount' => number_format($invoice->total_amount, 2, '.', ''),
                        'currency' => 'BDT',
                        'intent' => 'sale',
                        'merchantInvoiceNumber' => $invoice->invoice_number,
                    ]);

                if ($paymentResponse->successful()) {
                    break;
                }

                $retryCount++;
                if ($retryCount <= $maxRetries) {
                    sleep(1);
                }
            }

            if (! $paymentResponse || ! $paymentResponse->successful()) {
                $errorMsg = $paymentResponse ? ($paymentResponse->json('errorMessage') ?? 'Failed to create bKash payment') : 'No response from bKash';
                Log::error('bKash payment creation failed', [
                    'response' => $paymentResponse ? $paymentResponse->json() : null,
                    'status' => $paymentResponse ? $paymentResponse->status() : null,
                ]);
                throw new \Exception($errorMsg);
            }

            $paymentData = $paymentResponse->json();

            return [
                'success' => true,
                'payment_url' => $paymentData['bkashURL'] ?? '',
                'payment_id' => $paymentData['paymentID'] ?? '',
                'transaction_id' => $paymentData['paymentID'] ?? 'BK' . time() . random_int(1000, 9999),
                'amount' => $invoice->total_amount,
                'gateway' => 'bkash',
            ];
        } catch (\Exception $e) {
            Log::error('bKash payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'bkash',
            ];
        }
    }

    /**
     * Nagad payment initiation - Production Ready
     * Official Nagad API Documentation: https://developer.nagad.com.bd/
     */
    protected function initiateNagadPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode
                ? 'https://sandbox.mynagad.com:8094'
                : 'https://api.mynagad.com';

            $merchantId = $config['merchant_id'] ?? '';
            $merchantNumber = $config['merchant_number'] ?? '';
            $merchantPrivateKey = $config['merchant_private_key'] ?? '';
            $nagadPublicKey = $config['nagad_public_key'] ?? '';

            if (empty($merchantId) || empty($merchantPrivateKey) || empty($nagadPublicKey)) {
                throw new \Exception('Nagad credentials not configured');
            }

            $timestamp = now()->timestamp;
            $orderId = $invoice->invoice_number . '_' . $timestamp;
            $amount = number_format($invoice->total_amount, 2, '.', '');

            // Step 1: Initialize Payment
            $sensitiveData = [
                'merchantId' => $merchantId,
                'datetime' => date('YmdHis', $timestamp),
                'orderId' => $orderId,
                'challenge' => $this->generateNagadChallenge(),
            ];

            $postData = [
                'accountNumber' => $merchantNumber,
                'dateTime' => date('YmdHis', $timestamp),
                'sensitiveData' => $this->encryptNagadData($sensitiveData, $nagadPublicKey),
                'signature' => $this->generateNagadSignature($sensitiveData, $merchantPrivateKey),
            ];

            $initResponse = Http::timeout(30)
                ->withHeaders([
                    'X-KM-IP-V4' => request()->ip() ?? '0.0.0.0',
                    'X-KM-Client-Type' => 'PC_WEB',
                    'X-KM-Api-Version' => 'v-0.2.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/api/dfs/check-out/initialize/{$merchantId}/{$orderId}", $postData);

            if (! $initResponse->successful()) {
                $errorMsg = $initResponse->json('message') ?? 'Failed to initialize Nagad payment';
                Log::error('Nagad initialization failed', [
                    'response' => $initResponse->json(),
                    'status' => $initResponse->status(),
                ]);
                throw new \Exception($errorMsg);
            }

            $initData = $initResponse->json();
            $paymentReferenceId = $initData['paymentReferenceId'] ?? null;
            $challenge = $initData['challenge'] ?? null;

            if (empty($paymentReferenceId)) {
                throw new \Exception('Payment reference ID not received from Nagad');
            }

            // Step 2: Complete Payment
            $completeSensitiveData = [
                'merchantId' => $merchantId,
                'orderId' => $orderId,
                'amount' => $amount,
                'currencyCode' => '050', // BDT currency code
                'challenge' => $challenge,
            ];

            $completePostData = [
                'paymentReferenceId' => $paymentReferenceId,
                'callbackUrl' => route('webhooks.payment', ['gateway' => 'nagad']),
                'sensitiveData' => $this->encryptNagadData($completeSensitiveData, $nagadPublicKey),
                'signature' => $this->generateNagadSignature($completeSensitiveData, $merchantPrivateKey),
            ];

            $completeResponse = Http::timeout(30)
                ->withHeaders([
                    'X-KM-IP-V4' => request()->ip() ?? '0.0.0.0',
                    'X-KM-Client-Type' => 'PC_WEB',
                    'X-KM-Api-Version' => 'v-0.2.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/api/dfs/check-out/complete/{$paymentReferenceId}", $completePostData);

            if (! $completeResponse->successful()) {
                $errorMsg = $completeResponse->json('message') ?? 'Failed to complete Nagad payment';
                Log::error('Nagad completion failed', [
                    'response' => $completeResponse->json(),
                    'status' => $completeResponse->status(),
                ]);
                throw new \Exception($errorMsg);
            }

            $completeData = $completeResponse->json();

            return [
                'success' => true,
                'payment_url' => $completeData['callBackUrl'] ?? "{$baseUrl}/verify/payment/{$orderId}",
                'transaction_id' => $orderId,
                'payment_ref_id' => $paymentReferenceId,
                'amount' => $invoice->total_amount,
                'gateway' => 'nagad',
            ];
        } catch (\Exception $e) {
            Log::error('Nagad payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'nagad',
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
     * Encrypt Nagad sensitive data with public key
     */
    protected function encryptNagadData(array $data, string $publicKey): string
    {
        $dataString = json_encode($data);

        // Format public key properly
        if (! str_contains($publicKey, '-----BEGIN PUBLIC KEY-----')) {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($publicKey, 64, "\n") . '-----END PUBLIC KEY-----';
        }

        $encrypted = '';
        if (! openssl_public_encrypt($dataString, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('Nagad encryption failed: ' . openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    /**
     * Generate Nagad signature with private key
     */
    protected function generateNagadSignature(array $data, string $privateKey): string
    {
        $dataString = json_encode($data);

        // Format private key properly
        if (! str_contains($privateKey, '-----BEGIN PRIVATE KEY-----') && ! str_contains($privateKey, '-----BEGIN RSA PRIVATE KEY-----')) {
            $privateKey = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . '-----END PRIVATE KEY-----';
        }

        $signature = '';
        if (! openssl_sign($dataString, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('Nagad signature generation failed: ' . openssl_error_string());
        }

        return base64_encode($signature);
    }

    /**
     * Verify Nagad signature with public key
     */
    protected function verifyNagadSignature(string $data, string $signature, string $publicKey): bool
    {
        // Format public key properly
        if (! str_contains($publicKey, '-----BEGIN PUBLIC KEY-----')) {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($publicKey, 64, "\n") . '-----END PUBLIC KEY-----';
        }

        $result = openssl_verify($data, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }

    /**
     * SSLCommerz payment initiation - Production Ready
     * Official SSLCommerz API Documentation: https://developer.sslcommerz.com/
     */
    protected function initiateSSLCommerzPayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $baseUrl = $testMode
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';

            $storeId = $config['store_id'] ?? '';
            $storePassword = $config['store_password'] ?? '';

            if (empty($storeId) || empty($storePassword)) {
                throw new \Exception('SSLCommerz credentials not configured');
            }

            $tranId = 'SSL_' . time() . '_' . $invoice->invoice_number;

            $postData = [
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
                'total_amount' => number_format($invoice->total_amount, 2, '.', ''),
                'currency' => $config['currency'] ?? 'BDT',
                'tran_id' => $tranId,
                'success_url' => route('webhooks.payment', ['gateway' => 'sslcommerz', 'status' => 'success']),
                'fail_url' => route('webhooks.payment', ['gateway' => 'sslcommerz', 'status' => 'failed']),
                'cancel_url' => route('webhooks.payment', ['gateway' => 'sslcommerz', 'status' => 'cancelled']),
                'ipn_url' => route('webhooks.payment', ['gateway' => 'sslcommerz']),

                // Customer information
                'cus_name' => $invoice->networkUser->name ?? 'Customer',
                'cus_email' => $invoice->networkUser->email ?? 'customer@example.com',
                'cus_add1' => $invoice->networkUser->address ?? 'N/A',
                'cus_add2' => '',
                'cus_city' => $invoice->networkUser->city ?? 'Dhaka',
                'cus_state' => $invoice->networkUser->state ?? 'Dhaka',
                'cus_postcode' => $invoice->networkUser->postcode ?? '1000',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $invoice->networkUser->mobile ?? 'N/A',
                'cus_fax' => '',

                // Product information
                'product_name' => 'Internet Service - Invoice ' . $invoice->invoice_number,
                'product_category' => 'ISP Service',
                'product_profile' => 'general',

                // Shipment information (required by SSLCommerz)
                'shipping_method' => 'NO',
                'num_of_item' => 1,

                // Optional parameters - store custom data
                'value_a' => $invoice->invoice_number,
                'value_b' => (string) $invoice->network_user_id,
                'value_c' => (string) $invoice->id,
                'value_d' => (string) $invoice->tenant_id,
            ];

            $response = Http::timeout(30)
                ->asForm()
                ->post("{$baseUrl}/gwprocess/v4/api.php", $postData);

            if (! $response->successful()) {
                Log::error('SSLCommerz API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to initialize SSLCommerz payment');
            }

            $responseData = $response->json();

            if (! isset($responseData['status']) || $responseData['status'] !== 'SUCCESS') {
                $errorMsg = $responseData['failedreason'] ?? $responseData['message'] ?? 'SSLCommerz initialization failed';
                Log::error('SSLCommerz initialization failed', [
                    'response' => $responseData,
                ]);
                throw new \Exception($errorMsg);
            }

            return [
                'success' => true,
                'payment_url' => $responseData['GatewayPageURL'] ?? '',
                'transaction_id' => $tranId,
                'session_key' => $responseData['sessionkey'] ?? '',
                'amount' => $invoice->total_amount,
                'gateway' => 'sslcommerz',
            ];
        } catch (\Exception $e) {
            Log::error('SSLCommerz payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'sslcommerz',
            ];
        }
    }

    /**
     * Stripe payment initiation - Production Ready
     * Note: For production, install: composer require stripe/stripe-php
     * Official Stripe API Documentation: https://stripe.com/docs/api
     */
    protected function initiateStripePayment(Invoice $invoice, array $config, bool $testMode): array
    {
        try {
            $apiKey = $testMode
                ? ($config['test_secret_key'] ?? '')
                : ($config['secret_key'] ?? '');

            if (empty($apiKey)) {
                throw new \Exception('Stripe API key not configured');
            }

            $currency = strtolower($config['currency'] ?? 'usd');
            $amountInCents = (int) ($invoice->total_amount * 100);

            // Create a Checkout Session for hosted checkout page
            $sessionResponse = Http::timeout(30)
                ->withBasicAuth($apiKey, '')
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'line_items' => [[
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'Internet Service Invoice',
                                'description' => 'Invoice: ' . $invoice->invoice_number,
                            ],
                            'unit_amount' => $amountInCents,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('webhooks.payment', ['gateway' => 'stripe', 'status' => 'success']) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('webhooks.payment', ['gateway' => 'stripe', 'status' => 'cancelled']),
                    'client_reference_id' => $invoice->invoice_number,
                    'customer_email' => $invoice->networkUser->email ?? null,
                    'metadata[invoice_number]' => $invoice->invoice_number,
                    'metadata[invoice_id]' => $invoice->id,
                    'metadata[network_user_id]' => $invoice->network_user_id,
                    'metadata[tenant_id]' => $invoice->tenant_id,
                ]);

            if (! $sessionResponse->successful()) {
                $error = $sessionResponse->json('error');
                $errorMsg = $error['message'] ?? 'Failed to create Stripe checkout session';
                Log::error('Stripe checkout session creation failed', [
                    'error' => $error,
                    'status' => $sessionResponse->status(),
                ]);
                throw new \Exception($errorMsg);
            }

            $session = $sessionResponse->json();

            return [
                'success' => true,
                'payment_url' => $session['url'] ?? 'https://checkout.stripe.com',
                'session_id' => $session['id'] ?? '',
                'payment_intent_id' => $session['payment_intent'] ?? '',
                'transaction_id' => 'STRIPE_' . ($session['id'] ?? time()),
                'amount' => $invoice->total_amount,
                'gateway' => 'stripe',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed', [
                'invoice' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'stripe',
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
            if (! $this->verifyBkashWebhookSignature($payload)) {
                Log::warning('bKash webhook signature verification failed', $payload);

                return false;
            }

            // Process payment based on status
            if (isset($payload['paymentID']) && isset($payload['status'])) {
                $status = strtolower($payload['status']);
                $invoiceNumber = $payload['merchantInvoiceNumber'] ?? null;

                if (empty($invoiceNumber)) {
                    Log::warning('bKash webhook missing invoice number', $payload);

                    return false;
                }

                // First, find the invoice to get tenant_id
                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

                if (! $invoice) {
                    Log::warning('bKash webhook invoice not found', [
                        'invoice_number' => $invoiceNumber,
                        'payload' => $payload,
                    ]);

                    return false;
                }

                // Validate tenant context if provided in payload
                if (isset($payload['tenant_id']) && $payload['tenant_id'] != $invoice->tenant_id) {
                    Log::warning('bKash webhook tenant mismatch', [
                        'invoice_tenant' => $invoice->tenant_id,
                        'payload_tenant' => $payload['tenant_id'],
                    ]);

                    return false;
                }

                if (in_array($status, ['success', 'complete', 'completed'])) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'bkash',
                        'status' => 'completed',
                        'transaction_id' => $payload['trxID'] ?? $payload['paymentID'],
                        'gateway_response' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('bKash webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify bKash webhook signature
     * bKash uses HMAC SHA256 signature verification
     */
    protected function verifyBkashWebhookSignature(array $payload): bool
    {
        // Get signature from request header
        $signature = request()->header('X-Bkash-Signature');

        if (empty($signature)) {
            Log::warning('bKash webhook missing signature header');

            return false;
        }

        // Get gateway configuration
        $gateway = PaymentGateway::where('slug', 'bkash')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            Log::warning('bKash gateway not found or inactive');

            return false;
        }

        $config = $gateway->configuration ?? [];
        $webhookSecret = $config['webhook_secret'] ?? $config['app_secret'] ?? '';

        if (empty($webhookSecret)) {
            Log::warning('bKash webhook secret not configured');
            // In development, skip verification but log the decision
            if (app()->environment('local')) {
                Log::info('bKash webhook signature verification skipped in local environment');

                return true;
            }

            return false;
        }

        // Generate expected signature
        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $expectedSignature = hash_hmac('sha256', $payloadString, $webhookSecret);

        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process Nagad webhook with signature verification
     */
    protected function processNagadWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature
            if (! $this->verifyNagadWebhookSignature($payload)) {
                Log::warning('Nagad webhook signature verification failed', $payload);

                return false;
            }

            // Process payment based on status
            if (isset($payload['status']) && isset($payload['orderId'])) {
                $status = strtolower($payload['status']);
                $orderId = $payload['orderId'];

                // Extract invoice number from order ID (format: INVOICE_TIMESTAMP)
                $invoiceNumber = explode('_', $orderId)[0] ?? $orderId;

                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

                if (! $invoice) {
                    Log::warning('Nagad webhook invoice not found', [
                        'order_id' => $orderId,
                        'invoice_number' => $invoiceNumber,
                        'payload' => $payload,
                    ]);

                    return false;
                }

                if ($status === 'success') {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'nagad',
                        'status' => 'completed',
                        'transaction_id' => $payload['issuerPaymentRefNo'] ?? $payload['paymentRefId'] ?? $orderId,
                        'gateway_response' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Nagad webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify Nagad webhook signature
     * Nagad uses RSA signature verification with their public key
     */
    protected function verifyNagadWebhookSignature(array $payload): bool
    {
        // Get signature from request header
        $signature = request()->header('X-Nagad-Signature');

        if (empty($signature)) {
            Log::warning('Nagad webhook missing signature header');

            return false;
        }

        // Get gateway configuration
        $gateway = PaymentGateway::where('slug', 'nagad')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            Log::warning('Nagad gateway not found or inactive');

            return false;
        }

        $config = $gateway->configuration ?? [];
        $nagadPublicKey = $config['nagad_public_key'] ?? '';

        if (empty($nagadPublicKey)) {
            Log::warning('Nagad public key not configured');
            // In development, skip verification but log the decision
            if (app()->environment('local')) {
                Log::info('Nagad webhook signature verification skipped in local environment');

                return true;
            }

            return false;
        }

        // Verify signature using Nagad's public key
        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);

        try {
            return $this->verifyNagadSignature($payloadString, $signature, $nagadPublicKey);
        } catch (\Exception $e) {
            Log::error('Nagad signature verification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process SSLCommerz webhook with signature verification
     */
    protected function processSSLCommerzWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature
            if (! $this->verifySSLCommerzWebhookSignature($payload)) {
                Log::warning('SSLCommerz webhook signature verification failed', $payload);

                return false;
            }

            // Process payment based on status
            if (isset($payload['status']) && in_array($payload['status'], ['VALID', 'VALIDATED'])) {
                // Get invoice number from custom field value_a or tran_id
                $invoiceNumber = $payload['value_a'] ?? null;

                if (empty($invoiceNumber)) {
                    // Try to extract from transaction ID
                    $tranId = $payload['tran_id'] ?? '';
                    $parts = explode('_', $tranId);
                    $invoiceNumber = $parts[count($parts) - 1] ?? null;
                }

                if (empty($invoiceNumber)) {
                    Log::warning('SSLCommerz webhook missing invoice number', $payload);

                    return false;
                }

                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

                if (! $invoice) {
                    Log::warning('SSLCommerz webhook invoice not found', [
                        'invoice_number' => $invoiceNumber,
                        'payload' => $payload,
                    ]);

                    return false;
                }

                // Verify payment with SSLCommerz API
                $gateway = PaymentGateway::where('slug', 'sslcommerz')
                    ->where('tenant_id', $invoice->tenant_id)
                    ->where('is_active', true)
                    ->first();

                if (! $gateway) {
                    Log::warning('SSLCommerz gateway not found for tenant', [
                        'tenant_id' => $invoice->tenant_id,
                    ]);

                    return false;
                }

                // Validate transaction with SSLCommerz
                $validationResult = $this->verifySSLCommerzPayment(
                    $payload['val_id'] ?? $payload['tran_id'],
                    $gateway->configuration ?? []
                );

                if ($validationResult['verified'] ?? false) {
                    $this->billingService->processPayment($invoice, [
                        'amount' => $payload['amount'] ?? $invoice->total_amount,
                        'method' => 'sslcommerz',
                        'status' => 'completed',
                        'transaction_id' => $payload['tran_id'] ?? $payload['bank_tran_id'],
                        'gateway_response' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SSLCommerz webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify SSLCommerz webhook signature
     * SSLCommerz provides verify_sign and verify_key for validation
     */
    protected function verifySSLCommerzWebhookSignature(array $payload): bool
    {
        // Check if required verification fields are present
        if (! isset($payload['verify_sign']) || ! isset($payload['verify_key'])) {
            Log::warning('SSLCommerz webhook missing verification fields');

            return false;
        }

        // Get gateway configuration
        $gateway = PaymentGateway::where('slug', 'sslcommerz')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            Log::warning('SSLCommerz gateway not found or inactive');

            return false;
        }

        $config = $gateway->configuration ?? [];
        $storeId = $config['store_id'] ?? '';
        $storePassword = $config['store_password'] ?? '';

        if (empty($storeId) || empty($storePassword)) {
            Log::warning('SSLCommerz credentials not configured');

            return false;
        }

        // Verify MD5 signature
        if (! $this->verifySSLCommerzMD5Signature($payload, $storePassword)) {
            return false;
        }

        // Additional validation: Check status
        return in_array($payload['status'] ?? '', ['VALID', 'VALIDATED']);
    }

    /**
     * Verify SSLCommerz MD5 signature
     */
    protected function verifySSLCommerzMD5Signature(array $payload, string $storePassword): bool
    {
        // Generate MD5 hash for verification
        $verifyString = $storePassword . implode('', [
            $payload['val_id'] ?? '',
            $payload['store_id'] ?? '',
            $payload['store_amount'] ?? '',
            $payload['tran_id'] ?? '',
        ]);

        $expectedHash = md5($verifyString);

        // Verify hash matches
        if (! hash_equals($expectedHash, $payload['verify_sign'])) {
            Log::warning('SSLCommerz signature mismatch', [
                'expected' => $expectedHash,
                'received' => $payload['verify_sign'],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Process Stripe webhook with signature verification
     */
    protected function processStripeWebhook(array $payload): bool
    {
        try {
            // Verify Stripe webhook signature using Stripe-Signature header
            $signature = request()->header('Stripe-Signature');

            if (! $this->verifyStripeWebhookSignature($payload, $signature)) {
                Log::warning('Stripe webhook signature verification failed');

                return false;
            }

            // Handle different webhook events
            $eventType = $payload['type'] ?? '';

            if ($eventType === 'payment_intent.succeeded') {
                $paymentIntent = $payload['data']['object'] ?? [];
                $metadata = $paymentIntent['metadata'] ?? [];

                $invoiceNumber = $metadata['invoice_number'] ?? null;

                if (empty($invoiceNumber)) {
                    Log::warning('Stripe webhook missing invoice number', $payload);

                    return false;
                }

                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

                if (! $invoice) {
                    Log::warning('Stripe webhook invoice not found', [
                        'invoice_number' => $invoiceNumber,
                        'payment_intent' => $paymentIntent['id'] ?? null,
                    ]);

                    return false;
                }

                $this->billingService->processPayment($invoice, [
                    'amount' => ($paymentIntent['amount'] ?? 0) / 100,
                    'method' => 'stripe',
                    'status' => 'completed',
                    'transaction_id' => $paymentIntent['id'] ?? $payload['id'],
                    'gateway_response' => $payload,
                ]);

                return true;
            } elseif ($eventType === 'checkout.session.completed') {
                $session = $payload['data']['object'] ?? [];
                $metadata = $session['metadata'] ?? [];

                $invoiceNumber = $metadata['invoice_number'] ?? $session['client_reference_id'] ?? null;

                if (empty($invoiceNumber)) {
                    Log::warning('Stripe checkout webhook missing invoice number', $payload);

                    return false;
                }

                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

                if (! $invoice) {
                    Log::warning('Stripe checkout webhook invoice not found', [
                        'invoice_number' => $invoiceNumber,
                        'session_id' => $session['id'] ?? null,
                    ]);

                    return false;
                }

                if (($session['payment_status'] ?? '') === 'paid') {
                    $this->billingService->processPayment($invoice, [
                        'amount' => ($session['amount_total'] ?? 0) / 100,
                        'method' => 'stripe',
                        'status' => 'completed',
                        'transaction_id' => $session['payment_intent'] ?? $session['id'],
                        'gateway_response' => $payload,
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify Stripe webhook signature
     * Implements Stripe's webhook signature verification using HMAC SHA256
     */
    protected function verifyStripeWebhookSignature(array $payload, ?string $signature): bool
    {
        if (! $signature) {
            Log::warning('Stripe webhook missing signature header');

            return false;
        }

        // Parse signature header
        $signatureParts = [];
        foreach (explode(',', $signature) as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                [$key, $value] = $keyValue;
                $signatureParts[trim($key)] = trim($value);
            }
        }

        if (! isset($signatureParts['t']) || ! isset($signatureParts['v1'])) {
            Log::warning('Stripe webhook signature missing required parts');

            return false;
        }

        $timestamp = $signatureParts['t'];
        $receivedSignature = $signatureParts['v1'];

        // Check timestamp tolerance (5 minutes)
        if (abs(time() - $timestamp) > 300) {
            Log::warning('Stripe webhook signature timestamp out of tolerance', [
                'timestamp' => $timestamp,
                'current_time' => time(),
                'difference' => abs(time() - $timestamp),
            ]);

            return false;
        }

        // Get webhook secret from gateway config
        $gateway = PaymentGateway::where('slug', 'stripe')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            Log::warning('Stripe gateway not found or inactive');

            return false;
        }

        $config = $gateway->configuration ?? [];
        $webhookSecret = $config['webhook_secret'] ?? '';

        if (empty($webhookSecret)) {
            Log::warning('Stripe webhook secret not configured');
            // In development, skip verification but log the decision
            if (app()->environment('local')) {
                Log::info('Stripe webhook signature verification skipped in local environment');

                return true;
            }

            return false;
        }

        // Construct signed payload
        $rawPayload = request()->getContent();
        $signedPayload = $timestamp . '.' . $rawPayload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

        // Compare signatures using constant-time comparison
        if (! hash_equals($expectedSignature, $receivedSignature)) {
            Log::warning('Stripe webhook signature mismatch', [
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($receivedSignature, 0, 10) . '...',
            ]);

            return false;
        }

        return true;
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

            if (! $tokenResponse->successful()) {
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

            if (! $response->successful()) {
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

            if (! $response->successful()) {
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

            if (! $response->successful()) {
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

            if (! $response->successful()) {
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
