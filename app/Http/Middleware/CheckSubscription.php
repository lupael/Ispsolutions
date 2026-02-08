<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * B2B2B Subscription Enforcement Middleware
 *
 * Enforces subscription requirements for Super Admin users in the B2B2B model.
 * 
 * Usage:
 * - Add to 'api' middleware group for API routes
 * - Add to web guards for admin/super-admin panel routes
 *
 * Behavior:
 * - Allows Developers without subscription validation
 * - Validates Super Admin subscriptions (subscription_plan_id + expires_at)
 * - Prevents expired subscriptions from operating
 * - Logs subscription violations in audit trail
 *
 * HTTP Status Codes:
 * - 403: Subscription expired or missing (JSON API)
 * - Redirects to subscription page for web requests
 */
class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Allow unauthenticated requests to proceed
        if (!$user) {
            return $next($request);
        }

        // Developers don't need subscription validation
        if ($user->operator_level === $user::OPERATOR_LEVEL_DEVELOPER) {
            return $next($request);
        }

        // Only Super Admins require active subscriptions
        if ($user->operator_level === $user::OPERATOR_LEVEL_SUPER_ADMIN) {
            // Check if subscription_plan_id is set
            if (!$user->subscription_plan_id) {
                return $this->handleMissingSubscription($request, $user);
            }

            // Check if subscription hasn't expired
            if ($user->expires_at && now()->gt($user->expires_at)) {
                return $this->handleExpiredSubscription($request, $user);
            }
        }

        // All other users (Admins, Operators, etc.) inherit parent's subscription status
        return $next($request);
    }

    /**
     * Handle missing subscription
     */
    private function handleMissingSubscription(Request $request, $user): Response
    {
        // Log the violation
        \Log::warning('Super Admin without subscription attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'subscription_missing',
                'message' => 'Your account requires an active subscription. Please contact your platform provider.',
            ], 403);
        }

        return redirect()->route('subscription.plans')
            ->with('error', 'Your account requires an active subscription.');
    }

    /**
     * Handle expired subscription
     */
    private function handleExpiredSubscription(Request $request, $user): Response
    {
        // Log the violation
        \Log::warning('Expired subscription access attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'expires_at' => $user->expires_at,
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'subscription_expired',
                'message' => 'Your subscription expired on ' . $user->expires_at->format('Y-m-d'),
                'expires_at' => $user->expires_at->toIso8601String(),
            ], 403);
        }

        return redirect()->route('subscription.renew')
            ->with('error', 'Your subscription has expired. Please renew to continue.');
    }
}
