<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAutoDebitSettingsRequest;
use App\Jobs\ProcessAutoDebitJob;
use App\Models\AutoDebitHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Auto-Debit Controller
 *
 * Handles auto-debit settings and processing
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.2
 */
class AutoDebitController extends Controller
{
    /**
     * Display auto-debit settings for the authenticated user
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get auto-debit history
        $history = AutoDebitHistory::where('customer_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->paginate(15);

        return view('panels.customer.auto-debit.index', compact('user', 'history'));
    }

    /**
     * Get auto-debit settings (API)
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'auto_debit_enabled' => $user->auto_debit_enabled,
                'auto_debit_payment_method' => $user->auto_debit_payment_method,
                'auto_debit_max_retries' => $user->auto_debit_max_retries,
                'auto_debit_retry_count' => $user->auto_debit_retry_count,
                'auto_debit_last_attempt' => $user->auto_debit_last_attempt,
            ],
        ]);
    }

    /**
     * Update auto-debit settings
     */
    public function update(UpdateAutoDebitSettingsRequest $request): JsonResponse
    {
        $user = $request->user();

        $autoDebitEnabled = $request->boolean('auto_debit_enabled');
        $paymentMethod = $autoDebitEnabled ? $request->input('auto_debit_payment_method') : null;

        $user->update([
            'auto_debit_enabled' => $autoDebitEnabled,
            'auto_debit_payment_method' => $paymentMethod,
            'auto_debit_max_retries' => $request->integer('auto_debit_max_retries', 3),
        ]);

        // Reset retry count when re-enabling or updating settings
        if ($autoDebitEnabled) {
            $user->update(['auto_debit_retry_count' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto-debit settings updated successfully',
            'data' => [
                'auto_debit_enabled' => $user->auto_debit_enabled,
                'auto_debit_payment_method' => $user->auto_debit_payment_method,
                'auto_debit_max_retries' => $user->auto_debit_max_retries,
            ],
        ]);
    }

    /**
     * Get auto-debit history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = AutoDebitHistory::where('customer_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get failed auto-debit attempts report
     */
    public function failedReport(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admins and operators can view this report
        if (! $user->hasAnyRole(['admin', 'operator', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get failed auto-debit attempts
        $query = AutoDebitHistory::with('customer')
            ->where('status', 'failed');

        // Filter by customer if not admin
        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('operator_id', $user->id);
            });
        }

        $failedAttempts = $query
            ->orderBy('attempted_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $failedAttempts,
        ]);
    }

    /**
     * Manually trigger auto-debit for a customer (admin only)
     */
    public function trigger(Request $request, User $customer): JsonResponse
    {
        $user = $request->user();

        // Only admins can manually trigger auto-debit
        if (! $user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if customer has auto-debit enabled
        if (! $customer->auto_debit_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-debit is not enabled for this customer',
            ], 400);
        }

        // Dispatch job
        ProcessAutoDebitJob::dispatch($customer);

        return response()->json([
            'success' => true,
            'message' => 'Auto-debit process triggered successfully',
        ]);
    }

    /**
     * Reset retry count for a customer (admin only)
     */
    public function resetRetryCount(Request $request, User $customer): JsonResponse
    {
        $user = $request->user();

        // Only admins can reset retry count
        if (! $user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $customer->update(['auto_debit_retry_count' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Retry count reset successfully',
            'data' => [
                'customer_id' => $customer->id,
                'auto_debit_retry_count' => 0,
            ],
        ]);
    }
}
