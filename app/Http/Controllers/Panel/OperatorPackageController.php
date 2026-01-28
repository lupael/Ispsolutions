<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MasterPackage;
use App\Models\OperatorPackageRate;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Operator Package Controller
 * 
 * Handles operator-specific package rate management
 * Accessible by admin and operator roles
 */
class OperatorPackageController extends Controller
{
    /**
     * Display available master packages for operator
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get master packages available to this operator
        $query = MasterPackage::active()->public();
        
        // If operator has tenant, include tenant-specific and global packages
        if ($user->tenant_id) {
            $query->where(function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                  ->orWhereNull('tenant_id');
            });
        } else {
            $query->whereNull('tenant_id');
        }
        
        $masterPackages = $query->get();
        
        // Get operator's existing rates
        $operatorRates = OperatorPackageRate::where('operator_id', $user->id)
            ->with('masterPackage')
            ->get();

        return view('panels.admin.operator-packages.index', compact('masterPackages', 'operatorRates'));
    }

    /**
     * Show form to create operator-specific pricing for a master package
     */
    public function create(Request $request): View
    {
        $masterPackageId = $request->get('master_package_id');
        $masterPackage = MasterPackage::findOrFail($masterPackageId);
        
        $user = Auth::user();
        
        // Check if operator already has a rate for this master package
        $existingRate = OperatorPackageRate::where('operator_id', $user->id)
            ->where('master_package_id', $masterPackage->id)
            ->first();
        
        if ($existingRate) {
            return redirect()
                ->route('panel.admin.operator-packages.index')
                ->withErrors(['error' => 'You already have a rate configured for this master package.']);
        }

        return view('panels.admin.operator-packages.create', compact('masterPackage'));
    }

    /**
     * Store operator-specific pricing
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'master_package_id' => 'required|exists:master_packages,id',
            'operator_price' => 'required|numeric|min:1',
        ], [
            'operator_price.min' => __('packages.min_price_warning', ['min' => 1]),
        ]);

        $user = Auth::user();
        $masterPackage = MasterPackage::findOrFail($validated['master_package_id']);

        // Validate operator price doesn't exceed base price
        if ($validated['operator_price'] > $masterPackage->base_price) {
            return redirect()
                ->back()
                ->withErrors(['operator_price' => "Operator price cannot exceed base price of {$masterPackage->base_price}"])
                ->withInput();
        }

        // Check if already exists
        $existingRate = OperatorPackageRate::where('operator_id', $user->id)
            ->where('master_package_id', $masterPackage->id)
            ->first();

        if ($existingRate) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Rate already exists for this master package.'])
                ->withInput();
        }

        // Calculate margin
        $margin = $masterPackage->base_price > 0 
            ? (($validated['operator_price'] - $masterPackage->base_price) / $masterPackage->base_price) * 100 
            : 0;

        OperatorPackageRate::create([
            'tenant_id' => $user->tenant_id,
            'operator_id' => $user->id,
            'master_package_id' => $validated['master_package_id'],
            'operator_price' => $validated['operator_price'],
            'status' => 'active',
            'assigned_by' => $user->id,
        ]);

        $message = 'Operator package rate created successfully.';
        if ($margin < 10) {
            $message .= ' Warning: Your margin is below 10%.';
        }

        return redirect()
            ->route('panel.admin.operator-packages.index')
            ->with('success', $message);
    }

    /**
     * Show form to edit operator rate
     */
    public function edit(OperatorPackageRate $operatorRate): View
    {
        $user = Auth::user();
        
        // Ensure operator can only edit their own rates
        if ($operatorRate->operator_id !== $user->id) {
            abort(403, 'Unauthorized access to this operator rate.');
        }

        $masterPackage = $operatorRate->masterPackage;

        return view('panels.admin.operator-packages.edit', compact('operatorRate', 'masterPackage'));
    }

    /**
     * Update operator rate
     */
    public function update(Request $request, OperatorPackageRate $operatorRate): RedirectResponse
    {
        $user = Auth::user();
        
        // Ensure operator can only update their own rates
        if ($operatorRate->operator_id !== $user->id) {
            abort(403, 'Unauthorized access to this operator rate.');
        }

        $validated = $request->validate([
            'operator_price' => 'required|numeric|min:0',
        ]);

        $masterPackage = $operatorRate->masterPackage;

        // Validate operator price doesn't exceed base price
        if ($validated['operator_price'] > $masterPackage->base_price) {
            return redirect()
                ->back()
                ->withErrors(['operator_price' => "Operator price cannot exceed base price of {$masterPackage->base_price}"])
                ->withInput();
        }

        // Calculate margin
        $margin = $masterPackage->base_price > 0 
            ? (($validated['operator_price'] - $masterPackage->base_price) / $masterPackage->base_price) * 100 
            : 0;

        $operatorRate->update($validated);

        $message = 'Operator package rate updated successfully.';
        if ($margin < 10) {
            $message .= ' Warning: Your margin is below 10%.';
        }

        return redirect()
            ->route('panel.admin.operator-packages.index')
            ->with('success', $message);
    }

    /**
     * Delete operator rate
     */
    public function destroy(OperatorPackageRate $operatorRate): RedirectResponse
    {
        $user = Auth::user();
        
        // Ensure operator can only delete their own rates
        if ($operatorRate->operator_id !== $user->id) {
            abort(403, 'Unauthorized access to this operator rate.');
        }

        // Check if any packages are using this rate
        $packageCount = $operatorRate->packages()->count();
        if ($packageCount > 0) {
            return redirect()
                ->back()
                ->withErrors(['delete' => "Cannot delete: {$packageCount} package(s) are using this rate."]);
        }

        $operatorRate->delete();

        return redirect()
            ->route('panel.admin.operator-packages.index')
            ->with('success', 'Operator package rate deleted successfully.');
    }

    /**
     * Get suggested retail price
     */
    public function getSuggestedPrice(Request $request)
    {
        $operatorPrice = $request->get('operator_price', 0);
        $marginPercentage = $request->get('margin', 20);
        
        $suggestedPrice = round($operatorPrice * (1 + $marginPercentage / 100), 2);
        
        return response()->json([
            'suggested_price' => $suggestedPrice,
            'margin' => $marginPercentage,
        ]);
    }

    /**
     * Assign operator rate to sub-operator
     */
    public function assignToSubOperator(Request $request, OperatorPackageRate $operatorRate): RedirectResponse
    {
        $user = Auth::user();
        
        // Ensure this is the operator's own rate
        if ($operatorRate->operator_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'sub_operator_id' => 'required|exists:users,id',
            'sub_operator_price' => 'required|numeric|min:0',
        ]);

        // Validate sub-operator price doesn't exceed operator price
        if ($validated['sub_operator_price'] > $operatorRate->operator_price) {
            return redirect()
                ->back()
                ->withErrors(['sub_operator_price' => "Sub-operator price cannot exceed your price of {$operatorRate->operator_price}"])
                ->withInput();
        }

        // Check if sub-operator belongs to this operator or is a sub-operator
        $subOperator = User::findOrFail($validated['sub_operator_id']);
        if ($subOperator->created_by !== $user->id || $subOperator->operator_level !== User::OPERATOR_LEVEL_SUB_OPERATOR) {
            return redirect()
                ->back()
                ->withErrors(['sub_operator_id' => 'Invalid sub-operator. Must be created by you and have sub-operator level.'])
                ->withInput();
        }

        // Create rate for sub-operator
        OperatorPackageRate::create([
            'tenant_id' => $user->tenant_id,
            'operator_id' => $validated['sub_operator_id'],
            'master_package_id' => $operatorRate->master_package_id,
            'operator_price' => $validated['sub_operator_price'],
            'status' => 'active',
            'assigned_by' => $user->id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Package rate assigned to sub-operator successfully.');
    }
}
