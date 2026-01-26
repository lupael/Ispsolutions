<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BillingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BillingProfileController extends Controller
{
    /**
     * Display a listing of billing profiles.
     */
    public function index(): View
    {
        $profiles = BillingProfile::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return view('panels.admin.billing-profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new billing profile.
     */
    public function create(): View
    {
        return view('panels.admin.billing-profiles.create');
    }

    /**
     * Store a newly created billing profile.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:daily,monthly,free',
            'billing_day' => 'nullable|integer|min:1|max:31',
            'billing_time' => 'nullable|date_format:H:i',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'auto_generate_bill' => 'boolean',
            'auto_suspend' => 'boolean',
            'grace_period_days' => 'required|integer|min:0|max:365',
            'is_active' => 'boolean',
        ]);

        // Type-specific validation
        if ($validated['type'] === 'monthly' && empty($validated['billing_day'])) {
            return back()->withErrors(['billing_day' => 'Billing day is required for monthly profiles.'])->withInput();
        }

        if ($validated['type'] === 'daily' && empty($validated['billing_time'])) {
            return back()->withErrors(['billing_time' => 'Billing time is required for daily profiles.'])->withInput();
        }

        BillingProfile::create([
            ...$validated,
            'tenant_id' => auth()->user()->tenant_id,
            'auto_generate_bill' => $request->has('auto_generate_bill'),
            'auto_suspend' => $request->has('auto_suspend'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('panel.admin.billing-profiles.index')
            ->with('success', 'Billing profile created successfully.');
    }

    /**
     * Display the specified billing profile.
     */
    public function show(BillingProfile $billingProfile): View
    {
        // Ensure tenant isolation
        if ($billingProfile->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $billingProfile->loadCount('users');

        return view('panels.admin.billing-profiles.show', compact('billingProfile'));
    }

    /**
     * Show the form for editing the specified billing profile.
     */
    public function edit(BillingProfile $billingProfile): View
    {
        // Ensure tenant isolation
        if ($billingProfile->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('panels.admin.billing-profiles.edit', compact('billingProfile'));
    }

    /**
     * Update the specified billing profile.
     */
    public function update(Request $request, BillingProfile $billingProfile): RedirectResponse
    {
        // Ensure tenant isolation
        if ($billingProfile->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:daily,monthly,free',
            'billing_day' => 'nullable|integer|min:1|max:31',
            'billing_time' => 'nullable|date_format:H:i',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'auto_generate_bill' => 'boolean',
            'auto_suspend' => 'boolean',
            'grace_period_days' => 'required|integer|min:0|max:365',
            'is_active' => 'boolean',
        ]);

        // Type-specific validation
        if ($validated['type'] === 'monthly' && empty($validated['billing_day'])) {
            return back()->withErrors(['billing_day' => 'Billing day is required for monthly profiles.'])->withInput();
        }

        if ($validated['type'] === 'daily' && empty($validated['billing_time'])) {
            return back()->withErrors(['billing_time' => 'Billing time is required for daily profiles.'])->withInput();
        }

        $billingProfile->update([
            ...$validated,
            'auto_generate_bill' => $request->has('auto_generate_bill'),
            'auto_suspend' => $request->has('auto_suspend'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('panel.admin.billing-profiles.index')
            ->with('success', 'Billing profile updated successfully.');
    }

    /**
     * Remove the specified billing profile.
     */
    public function destroy(BillingProfile $billingProfile): RedirectResponse
    {
        // Ensure tenant isolation
        if ($billingProfile->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Check if profile can be deleted
        if (!$billingProfile->canDelete()) {
            return back()->withErrors(['error' => 'Cannot delete billing profile with assigned customers.']);
        }

        $billingProfile->delete();

        return redirect()->route('panel.admin.billing-profiles.index')
            ->with('success', 'Billing profile deleted successfully.');
    }
}
