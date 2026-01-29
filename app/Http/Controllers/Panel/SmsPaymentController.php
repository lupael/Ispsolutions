<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsPaymentRequest;
use App\Models\SmsPayment;
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
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
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
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Admins and superadmins can view all payments, others can only view their own
        $isAdmin = $user->hasAnyRole(['admin', 'superadmin']);
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
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
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

                // TODO: Send notification to operator about successful payment
                
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

                // TODO: Send notification to operator about failed payment
                
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
     */
    private function verifyBkashSignature(Request $request): bool
    {
        // TODO: Implement Bkash signature verification
        // This should verify the X-Signature header using Bkash's public key
        return false;
    }

    /**
     * Verify Nagad webhook signature
     */
    private function verifyNagadSignature(Request $request): bool
    {
        // TODO: Implement Nagad signature verification
        return false;
    }

    /**
     * Verify Rocket webhook signature
     */
    private function verifyRocketSignature(Request $request): bool
    {
        // TODO: Implement Rocket signature verification
        return false;
    }

    /**
     * Verify SSLCommerz webhook signature
     */
    private function verifySSLCommerzSignature(Request $request): bool
    {
        // TODO: Implement SSLCommerz signature verification
        return false;
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
        // TODO: Implement Bkash-specific data extraction when the exact webhook payload format is known.
        // For now, return null so that Bkash webhooks are treated as unsupported/invalid
        // rather than attempting to parse with incorrect or assumed field names.
        return null;
    }

    /**
     * Extract Nagad payment data from webhook
     */
    private function extractNagadData(Request $request): ?array
    {
        // TODO: Implement Nagad-specific data extraction
        return null;
    }

    /**
     * Extract Rocket payment data from webhook
     */
    private function extractRocketData(Request $request): ?array
    {
        // TODO: Implement Rocket-specific data extraction
        return null;
    }

    /**
     * Extract SSLCommerz payment data from webhook
     */
    private function extractSSLCommerzData(Request $request): ?array
    {
        // TODO: Implement SSLCommerz-specific data extraction
        return null;
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
     * Display SMS payment purchase page (Web UI)
     */
    public function webCreate(): View
    {
        $user = auth()->user();

        // Only operators, sub-operators, and admins can purchase SMS credits
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
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
