<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsPaymentRequest;
use App\Models\SmsPayment;
use App\Services\SmsBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only allow operators, sub-operators, and admins
        if (!$user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
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
     *
     * @param StoreSmsPaymentRequest $request
     * @return JsonResponse
     */
    public function store(StoreSmsPaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Create SMS payment record
        $payment = SmsPayment::create([
            'operator_id' => $user->id,
            'amount' => $request->input('amount'),
            'sms_quantity' => $request->input('sms_quantity'),
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
     *
     * @param SmsPayment $smsPayment
     * @return JsonResponse
     */
    public function show(SmsPayment $smsPayment): JsonResponse
    {
        $user = auth()->user();
        
        // Only allow operators, sub-operators, and admins
        if (!$user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Admins and superadmins can view all payments, others can only view their own
        $isAdmin = $user->hasAnyRole(['admin', 'superadmin']);
        if (!$isAdmin && $smsPayment->operator_id !== $user->id) {
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only allow operators, sub-operators, and admins
        if (!$user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        // TODO: CRITICAL - Implement webhook signature verification
        // Payment gateway webhooks MUST verify the request authenticity
        // This prevents unauthorized balance credits
        // Example for Bkash:
        // 1. Verify signature using gateway's public key
        // 2. Validate request IP against gateway's whitelist
        // 3. Check request timestamp to prevent replay attacks
        
        // Reject all requests until proper verification is implemented
        return response()->json([
            'success' => false,
            'message' => 'Webhook processing not yet implemented. Signature verification required.',
        ], 501);
        
        // TODO: After verification is implemented:
        // 1. Extract payment details from webhook payload
        // 2. Find the corresponding SmsPayment record
        // 3. Update payment status based on gateway response
        // 4. If successful, add SMS credits to operator balance using SmsBalanceService
        // 5. Send notification to operator about payment status
    }

    /**
     * Complete an SMS payment (admin/test use)
     * 
     * This endpoint allows manual completion of payments for testing
     *
     * @param SmsPayment $smsPayment
     * @return JsonResponse
     */
    public function complete(SmsPayment $smsPayment): JsonResponse
    {
        // Only allow if payment is pending
        if (!$smsPayment->isPending()) {
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
}
