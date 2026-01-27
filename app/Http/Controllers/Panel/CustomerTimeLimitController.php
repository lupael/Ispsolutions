<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerTimeLimit;
use App\Models\NetworkUser;
use App\Models\RadReply;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerTimeLimitController extends Controller
{
    /**
     * Display time limit for a customer.
     */
    public function show(User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $timeLimit = $customer->timeLimit;

        return view('panel.customers.time-limit.show', compact('customer', 'timeLimit'));
    }

    /**
     * Update or create time limit for a customer.
     */
    public function update(Request $request, User $customer, AuditLogService $auditLogService)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $request->validate([
            'daily_minutes_limit' => 'nullable|integer|min:0',
            'monthly_minutes_limit' => 'nullable|integer|min:0',
            'session_duration_limit' => 'nullable|integer|min:0',
            'allowed_start_time' => 'nullable|date_format:H:i',
            'allowed_end_time' => 'nullable|date_format:H:i',
            'auto_disconnect_on_limit' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $timeLimit = $customer->timeLimit;
            $oldValues = $timeLimit ? $timeLimit->toArray() : [];

            if ($timeLimit) {
                $timeLimit->update($request->only([
                    'daily_minutes_limit',
                    'monthly_minutes_limit',
                    'session_duration_limit',
                    'allowed_start_time',
                    'allowed_end_time',
                    'auto_disconnect_on_limit',
                ]));
            } else {
                $timeLimit = CustomerTimeLimit::create([
                    'user_id' => $customer->id,
                    'daily_minutes_limit' => $request->input('daily_minutes_limit'),
                    'monthly_minutes_limit' => $request->input('monthly_minutes_limit'),
                    'session_duration_limit' => $request->input('session_duration_limit'),
                    'allowed_start_time' => $request->input('allowed_start_time'),
                    'allowed_end_time' => $request->input('allowed_end_time'),
                    'auto_disconnect_on_limit' => $request->input('auto_disconnect_on_limit', true),
                    'day_reset_date' => now()->startOfDay(),
                    'month_reset_date' => now()->startOfMonth(),
                ]);
            }

            // Update RADIUS attributes for session timeout
            $networkUser = NetworkUser::where('user_id', $customer->id)->first();
            if ($networkUser && $networkUser->username) {
                $this->updateRadiusTimeLimits($networkUser->username, $timeLimit);
            }

            // Audit logging
            $auditLogService->logUpdated($timeLimit, $oldValues, $timeLimit->toArray());

            DB::commit();
            return back()->with('success', 'Time limit updated successfully. Customer needs to reconnect for changes to take effect.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update time limit', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to update time limit.']);
        }
    }

    /**
     * Update RADIUS attributes for time limits.
     */
    protected function updateRadiusTimeLimits(string $username, CustomerTimeLimit $timeLimit): void
    {
        try {
            // Update Session-Timeout attribute (for max session duration)
            if ($timeLimit->session_duration_limit > 0) {
                $timeoutSeconds = $timeLimit->session_duration_limit * 60;
                RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Session-Timeout'],
                    ['op' => ':=', 'value' => (string) $timeoutSeconds]
                );
            } else {
                // Remove if no limit
                RadReply::where('username', $username)
                    ->where('attribute', 'Session-Timeout')
                    ->delete();
            }

            // Update Idle-Timeout attribute (disconnect after idle period)
            $idleTimeoutSeconds = (int) config('radius.idle_timeout_seconds', 300);
            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Idle-Timeout'],
                ['op' => ':=', 'value' => (string) $idleTimeoutSeconds]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to update RADIUS time limits', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset time limit counters.
     */
    public function reset(Request $request, User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $request->validate([
            'reset_type' => 'required|in:daily,monthly,both',
        ]);

        $timeLimit = $customer->timeLimit;

        if (!$timeLimit) {
            return back()->withErrors(['error' => 'No time limit configured for this customer.']);
        }

        $resetType = $request->input('reset_type');

        if ($resetType === 'daily' || $resetType === 'both') {
            $timeLimit->update([
                'current_day_minutes' => 0,
                'day_reset_date' => now()->startOfDay(),
            ]);
        }

        if ($resetType === 'monthly' || $resetType === 'both') {
            $timeLimit->update([
                'current_month_minutes' => 0,
                'month_reset_date' => now()->startOfMonth(),
            ]);
        }

        return back()->with('success', 'Time limit reset successfully.');
    }

    /**
     * Remove time limit.
     */
    public function destroy(User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $timeLimit = $customer->timeLimit;

        if ($timeLimit) {
            $timeLimit->delete();
        }

        return back()->with('success', 'Time limit removed successfully.');
    }
}
