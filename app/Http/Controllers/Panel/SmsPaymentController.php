<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsPaymentRequest;
use App\Models\SmsPayment;
use App\Notifications\SmsPaymentFailedNotification;
use App\Notifications\SmsPaymentSuccessNotification;
use App\Services\SmsBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * SMS Payment Controller
 *
 * Handles SMS credit purchases and payment processing
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.1
 */
class SmsPaymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SmsBalanceService $smsBalanceService
    ) {}

    /**
     * Display a listing of SMS payments for the authenticated operator
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['isp', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only operators, sub-operators, and admins can access SMS payments.',
            ], 403);
        }

        $payments = SmsPayment::where('operator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Store a newly created SMS payment in storage
     */
    public function store(StoreSmsPaymentRequest $request): JsonResponse
    {
        $user = $request->user();

        // Calculate amount server-side based on quantity and pricing tiers
        $quantity = $request->integer('sms_quantity');
        $amount = $this->calculateSmsPrice($quantity);

        // Create SMS payment record
        $payment = SmsPayment::create([
            'operator_id' => $user->id,
            'amount' => $amount, // Server-calculated, not from user input
            'sms_quantity' => $quantity,
            'payment_method' => $request->input('payment_method'),
            'status' => 'pending',
        ]);

        // TODO: Initiate payment gateway transaction
        // This will be implemented when integrating with payment gateways
        // For now, we'll just create the payment record

        return response()->json([
            'success' => true,
            'message' => 'SMS payment initiated successfully',
            'data' => $payment,
        ], 201);
    }

    /**
     * Display the specified SMS payment
     */
    public function show(SmsPayment $smsPayment): JsonResponse
    {
        $user = auth()->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['isp', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Admins and superadmins can view all payments, others can only view their own
        $isAdmin = $user->hasAnyRole(['isp', 'superadmin']);
        if (! $isAdmin && $smsPayment->operator_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $smsPayment,
        ]);
    }

    /**
     * Get SMS balance and history for the authenticated operator
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['isp', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only operators, sub-operators, and admins can access SMS balance.',
            ], 403);
        }

        $history = $this->smsBalanceService->getHistory($user, 20);
        $stats = $this->smsBalanceService->getUsageStats($user, 'month');

        return response()->json([
            'success' => true,
            'data' => [
                'current_balance' => $user->sms_balance ?? 0,
                'low_balance_threshold' => $user->sms_low_balance_threshold ?? 100,
                'is_low_balance' => $user->hasLowSmsBalance(),
                'history' => $history,
                'monthly_stats' => $stats,
            ],
        ]);
    }

    /**
     * Handle payment gateway webhook/callback
     *
     * This method will be called by payment gateways to update payment status
     * NOTE: This endpoint requires webhook signature verification before processing
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Extract payment gateway from request
            $gateway = $request->input('gateway', 'bkash');

            // SECURITY: Verify webhook signature based on gateway
            $isValid = $this->verifyWebhookSignature($request, $gateway);

            if (!$isValid) {
                Log::warning('Invalid webhook signature detected', [
                    'gateway' => $gateway,
                    'ip' => $request->ip(),
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 403);
            }

            // Extract payment details from webhook payload
            $paymentData = $this->extractPaymentData($request, $gateway);

            if (!$paymentData) {
                Log::error('Failed to extract payment data from webhook', [
                    'gateway' => $gateway,
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload',
                ], 400);
            }

            // Find the corresponding SmsPayment record
            $paymentId = $paymentData['payment_id'] ?? null;
            $transactionId = $paymentData['transaction_id'] ?? null;

            $payment = null;

            // Prefer lookup by local payment ID if available
            if ($paymentId !== null) {
                $payment = SmsPayment::find($paymentId);
            }

            // Fallback to lookup by external transaction ID if needed
            if (!$payment && $transactionId !== null) {
                $payment = SmsPayment::where('transaction_id', $transactionId)->first();
            }

            if (!$payment) {
                Log::error('SMS payment not found for webhook', [
                    'gateway' => $gateway,
                    'payment_id' => $paymentData['payment_id'] ?? null,
                    'transaction_id' => $paymentData['transaction_id'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 404);
            }

            // Prevent duplicate processing
            if ($payment->isCompleted()) {
                Log::info('Webhook received for already completed payment', [
                    'payment_id' => $payment->id,
                    'gateway' => $gateway,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed',
                ]);
            }

            // Update payment status based on gateway response
            if ($paymentData['status'] === 'success') {
                // Mark payment as completed
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $paymentData['transaction_id'],
                    'completed_at' => now(),
                ]);

                // Add SMS credits to operator balance
                $operator = $payment->operator;
                $this->smsBalanceService->addCredits(
                    $operator,
                    $payment->sms_quantity,
                    'purchase',
                    'sms_payment',
                    $payment->id,
                    'SMS payment completed via ' . $gateway . ': ' . $paymentData['transaction_id']
                );

                Log::info('SMS payment webhook processed successfully', [
                    'payment_id' => $payment->id,
                    'operator_id' => $operator->id,
                    'amount' => $payment->amount,
                    'sms_quantity' => $payment->sms_quantity,
                    'transaction_id' => $paymentData['transaction_id'],
                ]);

                // Send notification to operator about successful payment
                $operator->notify(new SmsPaymentSuccessNotification($payment->fresh()));

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                ]);
            } else {
                // Mark payment as failed
                $payment->markFailed($paymentData['failure_reason'] ?? 'Payment failed');

                Log::warning('SMS payment webhook reported failure', [
                    'payment_id' => $payment->id,
                    'reason' => $paymentData['failure_reason'] ?? 'Unknown',
                ]);

                // Send notification to operator about failed payment
                $payment->operator->notify(new SmsPaymentFailedNotification(
                    $payment,
                    $paymentData['failure_reason'] ?? 'Payment failed'
                ));

                return response()->json([
                    'success' => true,
                    'message' => 'Payment failure acknowledged',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS payment webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify webhook signature based on payment gateway
     */
    private function verifyWebhookSignature(Request $request, string $gateway): bool
    {
        // In test/local environments, skip signature verification
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        // Implement gateway-specific signature verification
        return match ($gateway) {
            'bkash' => $this->verifyBkashSignature($request),
            'nagad' => $this->verifyNagadSignature($request),
            'rocket' => $this->verifyRocketSignature($request),
            'sslcommerz' => $this->verifySSLCommerzSignature($request),
            default => false,
        };
    }

    /**
     * Verify Bkash webhook signature
     * bKash uses HMAC SHA256 signature verification
     */
    private function verifyBkashSignature(Request $request): bool
    {
        // Get signature from request header
        $signature = $request->header('X-Bkash-Signature');

        if (empty($signature)) {
            Log::warning('bKash webhook missing signature header');
            return false;
        }

        // Get webhook secret from config
        $webhookSecret = config('services.bkash.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('bKash webhook secret not configured');
            return false;
        }

        // Generate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Compare signatures using constant-time comparison
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Nagad webhook signature
     * Nagad uses RSA signature verification with their public key
     */
    private function verifyNagadSignature(Request $request): bool
    {
        // Get signature from request header
        $signature = $request->header('X-Nagad-Signature');

        if (empty($signature)) {
            Log::warning('Nagad webhook missing signature header');
            return false;
        }

        // Get Nagad public key from config
        $publicKey = config('services.nagad.public_key');

        if (empty($publicKey)) {
            Log::warning('Nagad public key not configured');
            return false;
        }

        // Normalize/ensure Nagad public key is in PEM format
        if (strpos($publicKey, 'BEGIN PUBLIC KEY') === false) {
            // Remove all whitespace and line breaks, then wrap as PEM
            $normalizedKey = preg_replace('/\s+/', '', (string) $publicKey);
            $normalizedKey = chunk_split($normalizedKey, 64, "\n");
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
                trim($normalizedKey) .
                "\n-----END PUBLIC KEY-----";
        }

        // Load the public key via OpenSSL to validate its format
        $publicKeyResource = openssl_pkey_get_public($publicKey);

        if ($publicKeyResource === false) {
            Log::warning('Nagad public key is invalid or not in PEM format');
            return false;
        }

        // Get payload
        $payload = $request->getContent();

        try {
            // Decode the signature from base64 (strict mode)
            $decodedSignature = base64_decode($signature, true);

            if ($decodedSignature === false) {
                Log::warning('Nagad signature is not valid base64');
                return false;
            }

            // Verify signature using Nagad's public key
            $verified = openssl_verify(
                $payload,
                $decodedSignature,
                $publicKeyResource,
                OPENSSL_ALGO_SHA256
            );

            if ($verified === 1) {
                return true;
            }

            Log::warning('Nagad signature verification failed', [
                'verified' => $verified
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Nagad signature verification error', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Verify Rocket webhook signature
     * Rocket uses HMAC SHA256 signature verification similar to bKash
     */
    private function verifyRocketSignature(Request $request): bool
    {
        // Get signature from request header
        $signature = $request->header('X-Rocket-Signature');

        if (empty($signature)) {
            Log::warning('Rocket webhook missing signature header');
            return false;
        }

        // Get webhook secret from config
        $webhookSecret = config('services.rocket.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('Rocket webhook secret not configured');
            return false;
        }

        // Generate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Compare signatures using constant-time comparison
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify SSLCommerz webhook signature
     * SSLCommerz uses plain MD5 hashing (not HMAC) with store password for signature verification
     */
    private function verifySSLCommerzSignature(Request $request): bool
    {
        // Get verification hash from request
        $receivedHash = $request->input('verify_sign') ?? $request->input('verifySign');

        if (empty($receivedHash)) {
            Log::warning('SSLCommerz webhook missing verification hash');
            return false;
        }

        // Get store password from config
        $storePassword = config('services.sslcommerz.store_password');

        if (empty($storePassword)) {
            Log::warning('SSLCommerz store password not configured');
            return false;
        }

        // Get relevant fields for signature verification (SSLCommerz specific)
        $valId = $request->input('val_id', '');
        $storeId = config('services.sslcommerz.store_id', '');
        $amount = $request->input('amount', '');
        $tranId = $request->input('tran_id', '');
        $status = $request->input('status', '');

        // Build verification string according to SSLCommerz specification.
        // NOTE: The use of MD5 and this exact concatenation order are mandated by
        // SSLCommerz's official documentation for webhook/IPN verification. This is
        // not a general-purpose security design choice of this application and
        // MUST NOT be changed unless SSLCommerz changes their specification.
        $verificationString = $storePassword . $valId . $storeId . $amount . $tranId . $status;

        // Generate expected hash as required by SSLCommerz
        $expectedHash = md5($verificationString);

        // Compare hashes using constant-time comparison
        return hash_equals(strtolower($expectedHash), strtolower($receivedHash));
    }

    /**
     * Extract local payment ID from merchant invoice/order identifier
     * Handles formats: "SmsPayment-{id}" or just the numeric ID
     *
     * @param string|null $identifier The merchant invoice or order identifier
     * @return int|null The extracted payment ID or null if invalid
     */
    private function extractLocalPaymentId(?string $identifier): ?int
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        // Check for "SmsPayment-" prefix
        if (str_contains($identifier, 'SmsPayment-')) {
            $idPart = str_replace('SmsPayment-', '', $identifier);
            if ($idPart !== '' && ctype_digit($idPart)) {
                $localPaymentId = (int) $idPart;
                if ($localPaymentId > 0) {
                    return $localPaymentId;
                }
            }

            Log::warning('Unexpected SmsPayment identifier format', [
                'identifier' => $identifier,
            ]);
            return null;
        }

        // Check if it's a plain numeric ID
        if (is_numeric($identifier) && ctype_digit($identifier)) {
            $localPaymentId = (int) $identifier;
            if ($localPaymentId > 0) {
                return $localPaymentId;
            }
        }

        Log::warning('Unable to extract payment ID from identifier', [
            'identifier' => $identifier,
        ]);
        return null;
    }

    /**
     * Extract payment data from webhook payload
     */
    private function extractPaymentData(Request $request, string $gateway): ?array
    {
        // Extract data based on gateway-specific payload format
        return match ($gateway) {
            'bkash' => $this->extractBkashData($request),
            'nagad' => $this->extractNagadData($request),
            'rocket' => $this->extractRocketData($request),
            'sslcommerz' => $this->extractSSLCommerzData($request),
            default => null,
        };
    }

    /**
     * Extract Bkash payment data from webhook
     */
    private function extractBkashData(Request $request): ?array
    {
        $payload = $request->all();

        // bKash webhook payload format
        // Reference: bKash Payment Gateway API documentation
        $paymentId = $payload['paymentID'] ?? null;
        $tranId = $payload['trxID'] ?? null;
        $amount = $payload['amount'] ?? null;

        // bKash may return status in different fields - check both
        $rawStatus = $payload['paymentExecuteStatus'] ?? $payload['transactionStatus'] ?? '';
        $status = strtolower(trim((string) $rawStatus));

        // Extract local payment ID from merchantInvoiceNumber using helper
        $merchantInvoice = $payload['merchantInvoiceNumber'] ?? null;
        $localPaymentId = $this->extractLocalPaymentId($merchantInvoice);

        if (!$paymentId || !$status) {
            Log::warning('Invalid bKash webhook payload - missing required fields', [
                'has_paymentID' => !empty($paymentId),
                'has_status' => !empty($status),
            ]);
            return null;
        }

        // bKash success statuses - verify exact values from API docs
        $successStatuses = ['success', 'completed', 'complete'];
        $isSuccess = in_array($status, $successStatuses);

        return [
            'payment_id' => $localPaymentId,
            'transaction_id' => $tranId ?? $paymentId,
            'status' => $isSuccess ? 'success' : 'failed',
            'failure_reason' => !$isSuccess
                ? ($payload['statusMessage'] ?? $payload['message'] ?? 'Payment failed')
                : null,
        ];
    }

    /**
     * Extract Nagad payment data from webhook
     */
    private function extractNagadData(Request $request): ?array
    {
        $payload = $request->all();

        // Nagad webhook payload format
        // Reference: Nagad Payment Gateway API documentation
        $orderId = $payload['orderId'] ?? $payload['merchant_order_id'] ?? null;
        $paymentRefId = $payload['paymentRefId'] ?? $payload['payment_ref_id'] ?? null;
        $issuerPaymentRefNo = $payload['issuerPaymentRefNo'] ?? null;
        $amount = $payload['amount'] ?? null;

        $rawStatus = $payload['status'] ?? $payload['orderStatus'] ?? '';
        $status = strtolower(trim((string) $rawStatus));

        // Extract local payment ID using helper
        $localPaymentId = $this->extractLocalPaymentId($orderId);

        if (!$orderId || !$status) {
            Log::warning('Invalid Nagad webhook payload - missing required fields', [
                'has_orderId' => !empty($orderId),
                'has_status' => !empty($status),
            ]);
            return null;
        }

        // Nagad success statuses
        $successStatuses = ['success', 'paid', 'complete'];
        $isSuccess = in_array($status, $successStatuses);

        return [
            'payment_id' => $localPaymentId,
            'transaction_id' => $issuerPaymentRefNo ?? $paymentRefId ?? $orderId,
            'status' => $isSuccess ? 'success' : 'failed',
            'failure_reason' => !$isSuccess
                ? ($payload['message'] ?? $payload['error'] ?? 'Payment failed')
                : null,
        ];
    }

    /**
     * Extract Rocket payment data from webhook
     */
    private function extractRocketData(Request $request): ?array
    {
        $payload = $request->all();

        // Rocket webhook payload format
        // Reference: Dutch-Bangla Rocket Payment Gateway API documentation
        $tranId = $payload['trxId'] ?? $payload['transaction_id'] ?? null;
        $amount = $payload['amount'] ?? null;

        $rawStatus = $payload['status'] ?? $payload['transactionStatus'] ?? '';
        $status = strtolower(trim((string) $rawStatus));

        // Extract local payment ID using helper
        $merchantInvoice = $payload['merchantInvoiceNumber'] ?? $payload['merchant_invoice'] ?? null;
        $localPaymentId = $this->extractLocalPaymentId($merchantInvoice);

        if (!$tranId || !$status) {
            Log::warning('Invalid Rocket webhook payload - missing required fields', [
                'has_tranId' => !empty($tranId),
                'has_status' => !empty($status),
            ]);
            return null;
        }

        // Rocket success statuses
        $successStatuses = ['success', 'completed', 'paid', 'complete'];
        $isSuccess = in_array($status, $successStatuses);

        return [
            'payment_id' => $localPaymentId,
            'transaction_id' => $tranId,
            'status' => $isSuccess ? 'success' : 'failed',
            'failure_reason' => !$isSuccess
                ? ($payload['statusMessage'] ?? $payload['message'] ?? 'Payment failed')
                : null,
        ];
        }

    /**
     * Extract SSLCommerz payment data from webhook
     */
    private function extractSSLCommerzData(Request $request): ?array
    {
        $payload = $request->all();

        // SSLCommerz webhook payload format
        // Reference: SSLCommerz Payment Gateway API documentation
        $tranId = $payload['tran_id'] ?? null;
        $valId = $payload['val_id'] ?? null;
        $amount = $payload['amount'] ?? null;

        // SSLCommerz uses UPPERCASE status values
        $rawStatus = $payload['status'] ?? '';
        $status = strtoupper(trim((string) $rawStatus));

        // Extract local payment ID - SSLCommerz sends in value_a or tran_id
        $merchantInvoice = $payload['value_a'] ?? null;
        $localPaymentId = $this->extractLocalPaymentId($merchantInvoice);

        // Fallback: parse from transaction ID if value_a is not set
        if (!$localPaymentId && $tranId && str_contains($tranId, 'SmsPayment-')) {
            $parts = explode('SmsPayment-', $tranId);
            if (count($parts) > 1) {
                $localPaymentId = $this->extractLocalPaymentId($parts[1]);
            }
        }

        if (!$tranId || !$status) {
            Log::warning('Invalid SSLCommerz webhook payload - missing required fields', [
                'has_tranId' => !empty($tranId),
                'has_status' => !empty($status),
            ]);
            return null;
        }

        // SSLCommerz success statuses are UPPERCASE
        $successStatuses = ['VALID', 'VALIDATED'];
        $isSuccess = in_array($status, $successStatuses);

        return [
            'payment_id' => $localPaymentId,
            'transaction_id' => $valId ?? $tranId,
            'status' => $isSuccess ? 'success' : 'failed',
            'failure_reason' => !$isSuccess
                ? ($payload['error'] ?? $payload['failedReason'] ?? $payload['status_title'] ?? 'Payment failed')
                : null,
        ];
    }

    /**
     * Complete an SMS payment (admin/test use only)
     *
     * This endpoint allows manual completion of payments for testing
     */
    public function complete(SmsPayment $smsPayment): JsonResponse
    {
        $user = auth()->user();

        // Only superadmins can manually complete payments
        if (! $user->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only superadmins can manually complete payments.',
            ], 403);
        }

        // Only allow if payment is pending
        if (! $smsPayment->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment is not pending',
            ], 400);
        }

        // Mark payment as completed
        $smsPayment->markCompleted();

        // Add SMS credits to operator balance
        $operator = $smsPayment->operator;
        $this->smsBalanceService->addCredits(
            $operator,
            $smsPayment->sms_quantity,
            'purchase',
            'sms_payment',
            $smsPayment->id,
            'SMS payment completed: ' . $smsPayment->transaction_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully',
            'data' => [
                'payment' => $smsPayment->fresh(),
                'new_balance' => $operator->fresh()->sms_balance,
            ],
        ]);
    }

    /**
     * Display SMS payment history page (Web UI)
     */
    public function webIndex(Request $request): View
    {
        $user = $request->user();

        // Get paginated payments
        $payments = SmsPayment::where('operator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get balance information
        $balance = [
            'current_balance' => $user->sms_balance ?? 0,
            'low_balance_threshold' => $user->sms_low_balance_threshold ?? 100,
            'is_low_balance' => $user->hasLowSmsBalance(),
            'history' => $this->smsBalanceService->getHistory($user, 10),
            'monthly_stats' => $this->smsBalanceService->getUsageStats($user, 'month'),
        ];

        return view('panels.operator.sms-payments.index', compact('payments', 'balance'));
    }

    /**
     * Display SMS payment detail page (Web UI)
     */
    public function webShow(Request $request, SmsPayment $smsPayment): View
    {
        $user = $request->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['isp', 'operator', 'sub-operator', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }

        // Admins and superadmins can view all payments
        $isAdmin = $user->hasAnyRole(['isp', 'superadmin']);
        if (!$isAdmin) {
            // Determine which operator ID to check for non-admins
            $operatorIdToCheck = $user->id;

            // If user is a sub-operator and has a parent_operator_id, check against parent
            if ($user->hasRole('sub-operator') && isset($user->parent_operator_id)) {
                $operatorIdToCheck = $user->parent_operator_id;
            }

            // Check if user has access to this payment
            if ($smsPayment->operator_id !== $operatorIdToCheck) {
                abort(403, 'Unauthorized');
            }
        }

        // Load the operator relationship
        $smsPayment->load('operator');

        return view('panels.operator.sms-payments.show', compact('smsPayment'));
    }

    /**
     * Display SMS payment purchase page (Web UI)
     */
    public function webCreate(): View
    {
        $user = auth()->user();

        // Only operators, sub-operators, and admins can purchase SMS credits
        if (! $user->hasAnyRole(['isp', 'operator', 'sub-operator', 'superadmin'])) {
            abort(403, 'Unauthorized. Only operators can purchase SMS credits.');
        }

        return view('panels.operator.sms-payments.create');
    }

    /**
     * Calculate SMS price based on quantity and pricing tiers
     *
     * @param int $quantity Number of SMS credits
     *
     * @return float Calculated price in local currency
     */
    private function calculateSmsPrice(int $quantity): float
    {
        // Pricing tiers (per SMS in BDT)
        // TODO: Move these to config file or database for easier management
        if ($quantity >= 10000) {
            return $quantity * 0.40; // 20% discount
        } elseif ($quantity >= 5000) {
            return $quantity * 0.45; // 10% discount
        } else {
            return $quantity * 0.50; // Base rate
        }
    }
}
