<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerVolumeLimit;
use App\Models\NetworkUser;
use App\Models\RadReply;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerVolumeLimitController extends Controller
{
    /**
     * Display volume limit for a customer.
     */
    public function show(User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $volumeLimit = $customer->volumeLimit;

        return view('panel.customers.volume-limit.show', compact('customer', 'volumeLimit'));
    }

    /**
     * Update or create volume limit for a customer.
     */
    public function update(Request $request, User $customer, AuditLogService $auditLogService)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $request->validate([
            'monthly_limit_mb' => 'nullable|integer|min:0',
            'daily_limit_mb' => 'nullable|integer|min:0',
            'auto_suspend_on_limit' => 'boolean',
            'rollover_enabled' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $volumeLimit = $customer->volumeLimit;
            $oldValues = $volumeLimit ? $volumeLimit->toArray() : [];

            if ($volumeLimit) {
                $volumeLimit->update($request->only([
                    'monthly_limit_mb',
                    'daily_limit_mb',
                    'auto_suspend_on_limit',
                    'rollover_enabled',
                ]));
            } else {
                $volumeLimit = CustomerVolumeLimit::create([
                    'user_id' => $customer->id,
                    'monthly_limit_mb' => $request->input('monthly_limit_mb'),
                    'daily_limit_mb' => $request->input('daily_limit_mb'),
                    'auto_suspend_on_limit' => $request->input('auto_suspend_on_limit', true),
                    'rollover_enabled' => $request->input('rollover_enabled', false),
                    'month_reset_date' => now()->startOfMonth(),
                    'day_reset_date' => now()->startOfDay(),
                ]);
            }

            // Update RADIUS attributes for volume limits
            $networkUser = NetworkUser::where('user_id', $customer->id)->first();
            if ($networkUser && $networkUser->username) {
                $this->updateRadiusVolumeLimits($networkUser->username, $volumeLimit);
            }

            // Audit logging
            $auditLogService->logUpdated($volumeLimit, $oldValues, $volumeLimit->toArray());

            DB::commit();
            return back()->with('success', 'Volume limit updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update volume limit', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to update volume limit.']);
        }
    }

    /**
     * Update RADIUS attributes for volume limits.
     */
    protected function updateRadiusVolumeLimits(string $username, CustomerVolumeLimit $volumeLimit): void
    {
        try {
            // Update Mikrotik-Total-Limit attribute (for total data transfer limit)
            if ($volumeLimit->monthly_limit_mb > 0) {
                // Convert MB to bytes
                $limitBytes = $volumeLimit->monthly_limit_mb * 1024 * 1024;
                RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Mikrotik-Total-Limit'],
                    ['op' => ':=', 'value' => (string) $limitBytes]
                );
            } else {
                // Remove if no limit
                RadReply::where('username', $username)
                    ->where('attribute', 'Mikrotik-Total-Limit')
                    ->delete();
            }

            // Update daily limit as a RADIUS check attribute (custom implementation needed on RADIUS server)
            if ($volumeLimit->daily_limit_mb > 0) {
                $limitBytes = $volumeLimit->daily_limit_mb * 1024 * 1024;
                RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Daily-Octets-Limit'],
                    ['op' => ':=', 'value' => (string) $limitBytes]
                );
            } else {
                RadReply::where('username', $username)
                    ->where('attribute', 'Daily-Octets-Limit')
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update RADIUS volume limits', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset volume limit counters.
     */
    public function reset(Request $request, User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $request->validate([
            'reset_type' => 'required|in:daily,monthly,both',
        ]);

        $volumeLimit = $customer->volumeLimit;

        if (!$volumeLimit) {
            return back()->withErrors(['error' => 'No volume limit configured for this customer.']);
        }

        $resetType = $request->input('reset_type');

        if ($resetType === 'daily' || $resetType === 'both') {
            $volumeLimit->update([
                'current_day_usage_mb' => 0,
                'day_reset_date' => now()->startOfDay(),
            ]);
        }

        if ($resetType === 'monthly' || $resetType === 'both') {
            $volumeLimit->update([
                'current_month_usage_mb' => 0,
                'month_reset_date' => now()->startOfMonth(),
            ]);
        }

        return back()->with('success', 'Volume limit reset successfully.');
    }

    /**
     * Remove volume limit.
     */
    public function destroy(User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $volumeLimit = $customer->volumeLimit;

        if ($volumeLimit) {
            $volumeLimit->delete();
        }

        return back()->with('success', 'Volume limit removed successfully.');
    }
}
