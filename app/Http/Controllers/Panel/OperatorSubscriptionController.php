<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperatorSubscriptionRequest;
use App\Models\OperatorSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

/**
 * Operator Subscription Controller
 *
 * Handles operator platform subscription management and billing
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.3
 */
class OperatorSubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions for the authenticated operator
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
            abort(403, 'Unauthorized. Only operators can access subscriptions.');
        }

        // Build subscriptions query; admins/superadmins see all, others see only their own
        $query = OperatorSubscription::with('plan');

        if (! $user->hasAnyRole(['admin', 'superadmin'])) {
            $query->where('operator_id', $user->id);
        }

        $subscriptions = $query
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get subscription plans
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('panels.operator.operator-subscriptions.index', compact('subscriptions', 'plans'));
    }

    /**
     * Display subscription details
     */
    public function show(OperatorSubscription $subscription): View
    {
        $user = auth()->user();

        // Authorize access
        if (! $user->hasAnyRole(['admin', 'superadmin']) && $subscription->operator_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $subscription->load(['plan', 'payments']);

        return view('panels.operator.operator-subscriptions.show', compact('subscription'));
    }

    /**
     * Show subscription creation form
     */
    public function create(): View
    {
        $user = auth()->user();

        // Only operators, sub-operators, and admins can create subscriptions
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }

        // Get available subscription plans
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('panels.operator.operator-subscriptions.create', compact('plans'));
    }

    /**
     * Create a new subscription
     */
    public function store(StoreOperatorSubscriptionRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Check if operator already has an active subscription
            $existingSubscription = OperatorSubscription::where('operator_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription. Please cancel it before creating a new one.',
                ], 400);
            }

            // Get subscription plan
            $plan = SubscriptionPlan::findOrFail($request->integer('subscription_plan_id'));
            $billingCycle = $request->integer('billing_cycle', 1);

            // Calculate dates
            $startDate = now();
            $endDate = $startDate->copy()->addMonths($billingCycle);
            $nextBillingDate = $endDate;

            // Create subscription
            $subscription = OperatorSubscription::create([
                'operator_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'started_at' => $startDate,
                'expires_at' => $endDate,
                'billing_cycle' => $billingCycle,
                'next_billing_date' => $nextBillingDate,
                'auto_renew' => $request->boolean('auto_renew', true),
            ]);

            // Calculate amount based on billing cycle
            $amount = $plan->price * $billingCycle;

            // Create initial payment record
            $payment = SubscriptionPayment::create([
                'operator_subscription_id' => $subscription->id,
                'operator_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $request->input('payment_method'),
                'status' => 'pending',
                'billing_period_start' => $startDate,
                'billing_period_end' => $endDate,
            ]);

            // Generate invoice number
            $payment->generateInvoiceNumber();

            // TODO: Initiate payment gateway transaction
            // This will be implemented when integrating with payment gateways

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'subscription' => $subscription,
                    'payment' => $payment,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Subscription creation failed', [
                'operator_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancel(OperatorSubscription $subscription): JsonResponse
    {
        $user = auth()->user();

        // Authorize access
        if (! $user->hasAnyRole(['admin', 'superadmin']) && $subscription->operator_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if already cancelled
        if ($subscription->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already cancelled',
            ], 400);
        }

        // Cancel subscription
        $subscription->markCancelled();

        Log::info('Subscription cancelled', [
            'subscription_id' => $subscription->id,
            'operator_id' => $subscription->operator_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'data' => $subscription->fresh(),
        ]);
    }

    /**
     * Reactivate a suspended subscription
     */
    public function reactivate(OperatorSubscription $subscription): JsonResponse
    {
        $user = auth()->user();

        // Only superadmins can reactivate subscriptions
        if (! $user->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only superadmins can reactivate subscriptions.',
            ], 403);
        }

        // Check if subscription is suspended
        if (! $subscription->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Only suspended subscriptions can be reactivated',
            ], 400);
        }

        // Reactivate subscription
        $subscription->reactivate();

        Log::info('Subscription reactivated', [
            'subscription_id' => $subscription->id,
            'operator_id' => $subscription->operator_id,
            'reactivated_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription reactivated successfully',
            'data' => $subscription->fresh(),
        ]);
    }

    /**
     * Get subscription payment history
     */
    public function payments(OperatorSubscription $subscription): JsonResponse
    {
        $user = auth()->user();

        // Authorize access
        if (! $user->hasAnyRole(['admin', 'superadmin']) && $subscription->operator_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $payments = $subscription->payments()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Complete a subscription payment (admin/test use only)
     */
    public function completePayment(SubscriptionPayment $payment): JsonResponse
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
        if (! $payment->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment is not pending',
            ], 400);
        }

        // Mark payment as completed
        $payment->markCompleted();

        // Update subscription expiration based on this payment's billing period
        $subscription = $payment->subscription;
        $billingPeriodEnd = $payment->billing_period_end ?? null;

        if ($billingPeriodEnd) {
            $currentExpiry = $subscription->expires_at;

            // Only move the expiration date forward, never backwards
            if (! $currentExpiry || $billingPeriodEnd->greaterThan($currentExpiry)) {
                $subscription->expires_at = $billingPeriodEnd;
                $subscription->save();
            }
        } else {
            // We cannot safely adjust the expiration without billing period metadata
            Log::warning('Completed subscription payment without billing period metadata; expires_at not updated', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'completed_by' => $user->id,
            ]);
        }

        Log::info('Subscription payment completed manually', [
            'payment_id' => $payment->id,
            'subscription_id' => $subscription->id,
            'completed_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully',
            'data' => [
                'payment' => $payment->fresh(),
                'subscription' => $subscription->fresh(),
            ],
        ]);
    }

    /**
     * Get subscription statistics (API)
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only allow operators, sub-operators, and admins
        if (! $user->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get statistics
        $activeSubscriptions = OperatorSubscription::where('operator_id', $user->id)
            ->active()
            ->count();

        $totalPayments = SubscriptionPayment::where('operator_id', $user->id)
            ->completed()
            ->sum('amount');

        $pendingPayments = SubscriptionPayment::where('operator_id', $user->id)
            ->pending()
            ->count();

        $currentSubscription = OperatorSubscription::where('operator_id', $user->id)
            ->active()
            ->with('plan')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'active_subscriptions' => $activeSubscriptions,
                'total_payments' => $totalPayments,
                'pending_payments' => $pendingPayments,
                'current_subscription' => $currentSubscription,
                'days_until_expiration' => $currentSubscription?->getDaysUntilExpiration(),
                'is_about_to_expire' => $currentSubscription?->isAboutToExpire() ?? false,
            ],
        ]);
    }
}
