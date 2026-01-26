<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerVolumeLimit;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function update(Request $request, User $customer)
    {
        $this->authorize('editSpeedLimit', $customer);
        
        $request->validate([
            'monthly_limit_mb' => 'nullable|integer|min:0',
            'daily_limit_mb' => 'nullable|integer|min:0',
            'auto_suspend_on_limit' => 'boolean',
            'rollover_enabled' => 'boolean',
        ]);

        $volumeLimit = $customer->volumeLimit;

        if ($volumeLimit) {
            $volumeLimit->update($request->only([
                'monthly_limit_mb',
                'daily_limit_mb',
                'auto_suspend_on_limit',
                'rollover_enabled',
            ]));
        } else {
            CustomerVolumeLimit::create([
                'user_id' => $customer->id,
                'monthly_limit_mb' => $request->input('monthly_limit_mb'),
                'daily_limit_mb' => $request->input('daily_limit_mb'),
                'auto_suspend_on_limit' => $request->input('auto_suspend_on_limit', true),
                'rollover_enabled' => $request->input('rollover_enabled', false),
                'month_reset_date' => now()->startOfMonth(),
                'day_reset_date' => now()->startOfDay(),
            ]);
        }

        return back()->with('success', 'Volume limit updated successfully.');
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
