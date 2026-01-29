<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BkashAgreement;
use App\Services\BkashTokenizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Bkash Agreement Controller
 *
 * Handles Bkash tokenization agreements for one-click payments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Bkash Tokenization
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.4
 */
class BkashAgreementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BkashTokenizationService $bkashService
    ) {}

    /**
     * Display a listing of saved payment methods
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $agreements = BkashAgreement::where('user_id', $user->id)
            ->with('tokens')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('panels.payment-methods.index', compact('agreements'));
    }

    /**
     * Show the form for creating a new agreement
     */
    public function create(): View
    {
        return view('panels.payment-methods.create');
    }

    /**
     * Store a newly created agreement
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^01[3-9]\d{8}$/',
        ]);

        $user = $request->user();
        $mobileNumber = $request->input('mobile_number');

        // Generate callback URL
        $callbackUrl = route('panel.bkash-agreements.callback');

        // Create agreement via Bkash service
        $result = $this->bkashService->createAgreement($user, $mobileNumber, $callbackUrl);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to create Bkash agreement',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bkash agreement initiated successfully',
            'data' => [
                'agreement_id' => $result['agreement_id'],
                'bkash_url' => $result['bkash_url'],
                'redirect_url' => $result['bkash_url'], // For client-side redirect
            ],
        ], 201);
    }

    /**
     * Handle Bkash callback after agreement authorization
     */
    public function callback(Request $request): View
    {
        $status = $request->input('status');
        $paymentId = $request->input('paymentID');
        $agreementId = $request->input('agreementID');

        // Check if agreement was successful
        if ($status !== 'success') {
            return view('panels.payment-methods.callback', [
                'success' => false,
                'message' => 'Agreement creation was cancelled or failed',
            ]);
        }

        try {
            // Find the agreement
            $agreement = BkashAgreement::where('agreement_id', $agreementId)
                ->orWhere('payment_id', $paymentId)
                ->firstOrFail();

            // Execute the agreement
            $result = $this->bkashService->executeAgreement($agreement, $paymentId);

            if (!$result['success']) {
                return view('panels.payment-methods.callback', [
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to execute agreement',
                ]);
            }

            Log::info('Bkash agreement executed successfully', [
                'user_id' => $agreement->user_id,
                'agreement_id' => $agreement->agreement_id,
                'payment_id' => $paymentId,
            ]);

            return view('panels.payment-methods.callback', [
                'success' => true,
                'message' => 'Payment method added successfully!',
                'agreement' => $agreement->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Bkash agreement callback failed', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'agreement_id' => $agreementId,
            ]);

            return view('panels.payment-methods.callback', [
                'success' => false,
                'message' => 'An error occurred while processing your request',
            ]);
        }
    }

    /**
     * Display the specified agreement
     */
    public function show(BkashAgreement $agreement): JsonResponse
    {
        $user = auth()->user();

        // Ensure user owns this agreement
        if ($agreement->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'agreement' => $agreement->load('tokens'),
            ],
        ]);
    }

    /**
     * Cancel the specified agreement
     */
    public function destroy(BkashAgreement $agreement): JsonResponse
    {
        $user = auth()->user();

        // Ensure user owns this agreement
        if ($agreement->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Don't allow cancellation of already cancelled/expired agreements
        if ($agreement->isCancelled() || $agreement->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Agreement is already cancelled or expired',
            ], 400);
        }

        try {
            // Cancel agreement with Bkash (if needed)
            // TODO: Implement Bkash API call to cancel agreement if required

            // Mark agreement as cancelled locally
            $agreement->markCancelled();

            Log::info('Bkash agreement cancelled', [
                'user_id' => $agreement->user_id,
                'agreement_id' => $agreement->agreement_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel Bkash agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payment method',
            ], 500);
        }
    }

    /**
     * Get active payment methods for the authenticated user (API)
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeAgreements = BkashAgreement::where('user_id', $user->id)
            ->active()
            ->with('tokens')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => $activeAgreements,
                'count' => $activeAgreements->count(),
            ],
        ]);
    }
}
