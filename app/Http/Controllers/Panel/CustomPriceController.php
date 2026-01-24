<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomPrice;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomPriceController extends Controller
{
    /**
     * Display custom prices for a customer.
     */
    public function index(User $customer)
    {
        $customPrices = $customer->customPrices()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('panel.customers.custom-prices.index', compact('customer', 'customPrices'));
    }

    /**
     * Show form to create custom price.
     */
    public function create(User $customer)
    {
        $packages = Package::where('is_active', true)->get();

        return view('panel.customers.custom-prices.create', compact('customer', 'packages'));
    }

    /**
     * Store a new custom price.
     */
    public function store(Request $request, User $customer)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'custom_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'reason' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        // Check if custom price already exists for this package
        $exists = $customer->customPrices()
            ->where('package_id', $request->input('package_id'))
            ->exists();

        if ($exists) {
            return back()->withErrors(['package_id' => 'Custom price already exists for this package.']);
        }

        CustomPrice::create([
            'user_id' => $customer->id,
            'package_id' => $request->input('package_id'),
            'custom_price' => $request->input('custom_price'),
            'discount_percentage' => $request->input('discount_percentage'),
            'reason' => $request->input('reason'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => true,
            'approved_by' => Auth::id(),
        ]);

        return redirect()
            ->route('panel.customers.custom-prices.index', $customer)
            ->with('success', 'Custom price created successfully.');
    }

    /**
     * Show form to edit custom price.
     */
    public function edit(User $customer, CustomPrice $customPrice)
    {
        $packages = Package::where('is_active', true)->get();

        return view('panel.customers.custom-prices.edit', compact('customer', 'customPrice', 'packages'));
    }

    /**
     * Update custom price.
     */
    public function update(Request $request, User $customer, CustomPrice $customPrice)
    {
        $request->validate([
            'custom_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'reason' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
        ]);

        $customPrice->update($request->only([
            'custom_price',
            'discount_percentage',
            'reason',
            'valid_from',
            'valid_until',
            'is_active',
        ]));

        return redirect()
            ->route('panel.customers.custom-prices.index', $customer)
            ->with('success', 'Custom price updated successfully.');
    }

    /**
     * Remove custom price.
     */
    public function destroy(User $customer, CustomPrice $customPrice)
    {
        $customPrice->delete();

        return redirect()
            ->route('panel.customers.custom-prices.index', $customer)
            ->with('success', 'Custom price deleted successfully.');
    }
}
