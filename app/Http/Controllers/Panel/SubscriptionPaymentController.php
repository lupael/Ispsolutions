<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessSubscriptionPaymentRequest;
use App\Models\Subscription;
use App\Models\SubscriptionBill;
use App\Models\SubscriptionPlan;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Subscription Payment Controller
 *
 * Handles subscription billing and payment processing for platform operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.3
 */
class SubscriptionPaymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PaymentGatewayService $paymentGatewayService
    ) {}

    /**
     * Display subscription plans
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get available subscription plans
        $plans = SubscriptionPlan::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        // Get current subscription if exists
        $currentSubscription = null;
        if ($user->tenant_id) {
            $currentSubscription = Subscription::where('tenant_id', $user->tenant_id)
                ->whereIn('status', ['active', 'trial'])
                ->first();
        }

        return view('panels.operator.subscriptions.index', compact('plans', 'currentSubscription'));
    }

    /**
     * Show subscription plan details
     */
    public function show(SubscriptionPlan $plan): View
    {
        return view('panels.operator.subscriptions.show', compact('plan'));
    }

    /**
     * Create a new subscription
     */
    public function subscribe(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $user = $request->user();

        // Check if user has a tenant
        if (! $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'You must have a tenant account to subscribe',
            ], 400);
        }

        // Check if already subscribed
        $existingSubscription = Subscription::where('tenant_id', $user->tenant_id)
            ->whereIn('status', ['active', 'trial'])
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription',
            ], 400);
        }

        try {
            // Create subscription
            $endDate = $plan->trial_days > 0 
                ? now()->addDays($plan->trial_days)
                : now()->addDays($this->getBillingCycleDays($plan->billing_cycle));

            $subscription = Subscription::create([
                'tenant_id' => $user->tenant_id,
                'plan_id' => $plan->id,
                'status' => $plan->trial_days > 0 ? 'trial' : 'active',
                'start_date' => now(),
                'end_date' => $endDate,
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'amount' => $plan->price,
                'currency' => $plan->currency,
            ]);

            // Create first bill
            $bill = SubscriptionBill::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $user->tenant_id,
                'amount' => $plan->trial_days > 0 ? 0 : $plan->price,
                'currency' => $plan->currency,
                'billing_period_start' => now(),
                'billing_period_end' => now()->addDays($this->getBillingCycleDays($plan->billing_cycle)),
                'due_date' => now()->addDays(7),
                'status' => $plan->trial_days > 0 ? 'paid' : 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'subscription' => $subscription,
                    'bill' => $bill,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process subscription payment
     */
    public function processPayment(ProcessSubscriptionPaymentRequest $request, SubscriptionBill $bill): JsonResponse
    {
        $user = $request->user();

        // Verify bill belongs to user's tenant
        if ($bill->tenant_id !== $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if bill is already paid
        if ($bill->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Bill is already paid',
            ], 400);
        }

        try {
            // TODO: SECURITY - Integrate with payment gateway before production
            // This endpoint currently allows marking bills as paid without actual payment
            // In production, this should:
            // 1. Initiate payment via PaymentGatewayService
            // 2. Only mark as paid after receiving verified gateway response
            // 3. Or restrict this endpoint to admin/internal use only
            
            // For now, mark as paid (THIS IS NOT PRODUCTION-READY)
            Log::warning('Subscription bill marked as paid without gateway verification', [
                'bill_id' => $bill->id,
                'tenant_id' => $user->tenant_id,
                'amount' => $bill->amount,
                'environment' => app()->environment(),
            ]);

            $bill->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Update subscription status
            $subscription = $bill->subscription;
            if ($subscription && $subscription->status !== 'active') {
                $subscription->update(['status' => 'active']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'bill' => $bill->fresh(),
                    'subscription' => $subscription->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription bills
     */
    public function bills(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant found',
            ], 400);
        }

        $bills = SubscriptionBill::where('tenant_id', $user->tenant_id)
            ->with('subscription.plan')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bills,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant found',
            ], 400);
        }

        $subscription = Subscription::where('tenant_id', $user->tenant_id)
            ->whereIn('status', ['active', 'trial'])
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
            ], 404);
        }

        try {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get billing cycle days
     *
     * @param string $cycle
     * @return int
     */
    protected function getBillingCycleDays(string $cycle): int
    {
        return match ($cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365,
            default => 30,
        };
    }
}
