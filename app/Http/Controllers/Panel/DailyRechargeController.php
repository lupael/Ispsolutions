<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Services\DailyRechargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyRechargeController extends Controller
{
    public function __construct(
        protected DailyRechargeService $rechargeService
    ) {
    }

    /**
     * Show daily recharge form for a customer.
     */
    public function show(User $customer): View
    {
        $this->authorize('update', $customer);

        // Get available daily packages
        $dailyPackages = Package::where('billing_type', 'daily')
            ->where('status', 'active')
            ->get();

        // Get recharge history for this customer - use payments relationship
        $rechargeHistory = $customer->payments()
            ->where('notes', 'like', '%daily recharge%')
            ->latest()
            ->take(10)
            ->get();

        return view('panels.shared.customers.daily-recharge', compact(
            'customer',
            'dailyPackages',
            'rechargeHistory'
        ));
    }

    /**
     * Process daily recharge for a customer.
     */
    public function recharge(Request $request, User $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'days' => 'nullable|integer|min:1|max:30',
            'payment_method' => 'required|string|in:cash,card,online,wallet',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $package = Package::findOrFail($validated['package_id']);
            $days = $validated['days'] ?? 1;

            // Calculate amount
            $dailyRate = $this->rechargeService->calculateDailyRate($package);
            $amount = $dailyRate * $days;

            // Process recharge
            $result = $this->rechargeService->processDailyRecharge(
                $customer,
                $package,
                $days
            );

            return redirect()
                ->route('panel.admin.customers.daily-recharge.show', $customer)
                ->with('success', "Daily recharge successful. Package activated for {$days} day(s). Amount: " . number_format($amount, 2));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to process recharge: ' . $e->getMessage());
        }
    }

    /**
     * Get daily rate calculation via AJAX.
     */
    public function calculateRate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'days' => 'required|integer|min:1|max:30',
        ]);

        try {
            $package = Package::findOrFail($validated['package_id']);
            $days = $validated['days'];
            $dailyRate = $this->rechargeService->calculateDailyRate($package);
            $amount = $dailyRate * $days;

            return response()->json([
                'success' => true,
                'amount' => $amount,
                'formatted_amount' => number_format($amount, 2),
                'per_day' => number_format($dailyRate, 2),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show auto-renewal settings for a customer.
     */
    public function autoRenewal(User $customer): View
    {
        $this->authorize('update', $customer);

        return view('panels.shared.customers.auto-renewal', compact('customer'));
    }

    /**
     * Update auto-renewal settings.
     */
    public function updateAutoRenewal(Request $request, User $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'auto_renew_enabled' => 'required|boolean',
            'auto_renew_package_id' => 'nullable|exists:packages,id',
            'auto_renew_days' => 'nullable|integer|min:1|max:30',
        ]);

        try {
            $customer->update([
                'auto_renew_enabled' => $validated['auto_renew_enabled'],
                'auto_renew_package_id' => $validated['auto_renew_package_id'] ?? null,
                'auto_renew_days' => $validated['auto_renew_days'] ?? 1,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Auto-renewal settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
