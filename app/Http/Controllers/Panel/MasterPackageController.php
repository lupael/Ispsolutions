<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MasterPackage;
use App\Models\OperatorPackageRate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Master Package Controller
 * 
 * Handles CRUD operations for master packages
 * Accessible by developer, super-admin, and admin roles
 */
class MasterPackageController extends Controller
{
    /**
     * Determine the route prefix based on current route
     */
    protected function getRoutePrefix(): string
    {
        return $this->isAdminContext() 
            ? 'panel.admin.master-packages' 
            : 'panel.developer.master-packages';
    }

    /**
     * Determine the view prefix based on current route
     */
    protected function getViewPrefix(): string
    {
        return $this->isAdminContext()
            ? 'panels.admin.master-packages'
            : 'panels.developer.master-packages';
    }

    /**
     * Check if current request is in admin context
     */
    protected function isAdminContext(): bool
    {
        $currentRouteName = request()->route()->getName();
        return str_starts_with($currentRouteName, 'panel.admin.');
    }

    /**
     * Display a listing of master packages
     */
    public function index(Request $request): View
    {
        $query = MasterPackage::with(['creator', 'operatorRates']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by visibility
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        // Filter by trial package
        if ($request->filled('is_trial')) {
            $query->where('is_trial_package', $request->boolean('is_trial'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // If user is super-admin, filter by their tenant
        $user = Auth::user();
        if ($user->operator_level === User::OPERATOR_LEVEL_SUPER_ADMIN) {
            $query->where(function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                  ->orWhereNull('tenant_id'); // Include global packages
            });
        }

        $masterPackages = $query->orderBy('created_at', 'desc')->paginate(15);

        return view($this->getViewPrefix() . '.index', compact('masterPackages'));
    }

    /**
     * Show the form for creating a new master package
     */
    public function create(): View
    {
        return view($this->getViewPrefix() . '.create');
    }

    /**
     * Store a newly created master package
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'speed_upload' => 'nullable|integer|min:0',
            'speed_download' => 'nullable|integer|min:0',
            'volume_limit' => 'nullable|integer|min:0',
            'validity_days' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:1',
            'visibility' => 'required|in:public,private',
            'is_trial_package' => 'boolean',
            'status' => 'required|in:active,inactive',
        ], [
            'base_price.min' => 'Package price must be at least 1 to prevent free packages.',
        ]);

        $user = Auth::user();
        
        // Set tenant_id based on user role
        $validated['tenant_id'] = $user->operator_level === User::OPERATOR_LEVEL_SUPER_ADMIN 
            ? $user->tenant_id 
            : null;
        
        $validated['created_by'] = $user->id;
        $validated['is_trial_package'] = $request->boolean('is_trial_package', false);

        MasterPackage::create($validated);

        return redirect()
            ->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Master package created successfully.');
    }

    /**
     * Display the specified master package
     */
    public function show(MasterPackage $masterPackage): View
    {
        $masterPackage->load(['creator', 'operatorRates.operator', 'packages']);

        // Get usage statistics
        $stats = [
            'operator_count' => $masterPackage->operatorRates()->count(),
            'customer_count' => $masterPackage->customer_count,
            'total_revenue' => $masterPackage->packages()->sum('price'),
        ];

        return view($this->getViewPrefix() . '.show', compact('masterPackage', 'stats'));
    }

    /**
     * Show the form for editing the specified master package
     */
    public function edit(MasterPackage $masterPackage): View
    {
        return view($this->getViewPrefix() . '.edit', compact('masterPackage'));
    }

    /**
     * Update the specified master package
     */
    public function update(Request $request, MasterPackage $masterPackage): RedirectResponse
    {
        // Prevent modification of trial package pricing
        if ($masterPackage->is_trial_package && $request->filled('base_price')) {
            return redirect()
                ->back()
                ->withErrors(['base_price' => 'Cannot modify pricing on trial packages.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'speed_upload' => 'nullable|integer|min:0',
            'speed_download' => 'nullable|integer|min:0',
            'volume_limit' => 'nullable|integer|min:0',
            'validity_days' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:1',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:active,inactive',
        ], [
            'base_price.min' => 'Package price must be at least 1 to prevent free packages.',
        ]);

        $masterPackage->update($validated);

        return redirect()
            ->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Master package updated successfully.');
    }

    /**
     * Remove the specified master package
     */
    public function destroy(MasterPackage $masterPackage): RedirectResponse
    {
        if (!$masterPackage->canDelete()) {
            $reason = $masterPackage->getDeletionPreventionReason();
            return redirect()
                ->back()
                ->withErrors(['delete' => $reason]);
        }

        $masterPackage->delete();

        return redirect()
            ->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Master package deleted successfully.');
    }

    /**
     * Show form to assign master package to operators
     */
    public function assignToOperators(MasterPackage $masterPackage): View
    {
        // Get operators (Admin, Operator, Sub-Operator)
        $operators = User::whereIn('operator_level', [
            User::OPERATOR_LEVEL_ADMIN,
            User::OPERATOR_LEVEL_OPERATOR,
            User::OPERATOR_LEVEL_SUB_OPERATOR,
        ])->get();

        // Get already assigned operators
        $assignedOperatorIds = $masterPackage->operatorRates()->pluck('operator_id')->toArray();

        return view($this->getViewPrefix() . '.assign', compact('masterPackage', 'operators', 'assignedOperatorIds'));
    }

    /**
     * Assign master package to operator with pricing
     */
    public function storeOperatorAssignment(Request $request, MasterPackage $masterPackage): RedirectResponse
    {
        $validated = $request->validate([
            'operator_id' => 'required|exists:users,id',
            'operator_price' => 'required|numeric|min:1',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'operator_price.min' => 'Operator price must be at least 1.',
        ]);

        // Validate operator price doesn't exceed base price
        if ($validated['operator_price'] > $masterPackage->base_price) {
            return redirect()
                ->back()
                ->withErrors(['operator_price' => 'Operator price cannot exceed base price of ' . $masterPackage->base_price])
                ->withInput();
        }

        // Check if assignment already exists
        $existingRate = OperatorPackageRate::where('operator_id', $validated['operator_id'])
            ->where('master_package_id', $masterPackage->id)
            ->first();

        if ($existingRate) {
            return redirect()
                ->back()
                ->withErrors(['operator_id' => 'This operator is already assigned to this master package.'])
                ->withInput();
        }

        // Calculate margin percentage
        $margin = (($validated['operator_price'] - $masterPackage->base_price) / $masterPackage->base_price) * 100;
        $lowMarginWarning = $margin < 10 ? 'Warning: Margin is below 10%' : null;

        $user = Auth::user();
        
        OperatorPackageRate::create([
            'tenant_id' => $user->tenant_id,
            'operator_id' => $validated['operator_id'],
            'master_package_id' => $masterPackage->id,
            'operator_price' => $validated['operator_price'],
            'commission_percentage' => $validated['commission_percentage'] ?? 0,
            'status' => 'active',
            'assigned_by' => $user->id,
        ]);

        $message = 'Master package assigned to operator successfully.';
        if ($lowMarginWarning) {
            $message .= ' ' . $lowMarginWarning;
        }

        return redirect()
            ->route($this->getRoutePrefix() . '.show', $masterPackage)
            ->with('success', $message);
    }

    /**
     * Remove operator assignment
     */
    public function removeOperatorAssignment(MasterPackage $masterPackage, OperatorPackageRate $operatorRate): RedirectResponse
    {
        // Check if any packages are using this rate
        $packageCount = $operatorRate->packages()->count();
        if ($packageCount > 0) {
            return redirect()
                ->back()
                ->withErrors(['delete' => "Cannot remove: {$packageCount} package(s) are using this operator rate."]);
        }

        $operatorRate->delete();

        return redirect()
            ->back()
            ->with('success', 'Operator assignment removed successfully.');
    }

    /**
     * Get usage statistics for master package
     */
    public function stats(MasterPackage $masterPackage)
    {
        $stats = [
            'operators' => $masterPackage->operatorRates()->count(),
            'customers' => $masterPackage->customer_count,
            'packages' => $masterPackage->packages()->count(),
            'revenue' => [
                'total' => $masterPackage->packages()->sum('price'),
                'average' => $masterPackage->packages()->avg('price'),
            ],
            'operator_rates' => $masterPackage->operatorRates()
                ->with('operator')
                ->get()
                ->map(function ($rate) use ($masterPackage) {
                    return [
                        'operator' => $rate->operator->name,
                        'price' => $rate->operator_price,
                        'margin' => (($rate->operator_price - $masterPackage->base_price) / $masterPackage->base_price) * 100,
                        'customers' => $rate->packages()->count(),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Show package hierarchy tree view
     */
    public function hierarchy(Request $request): View
    {
        $hierarchyService = app(\App\Services\PackageHierarchyService::class);
        
        // Build package tree
        $packages = Package::with(['users', 'parentPackage', 'childPackages'])
            ->orderBy('price', 'asc')
            ->get();
        
        $packageTree = $hierarchyService->buildTree($packages);
        
        return view($this->getViewPrefix() . '.hierarchy', compact('packageTree'));
    }

    /**
     * Show package comparison view
     */
    public function comparison(Request $request): View
    {
        // Get package IDs from query string
        $packageIds = $request->input('packages', []);
        
        if (is_string($packageIds)) {
            $packageIds = explode(',', $packageIds);
        }
        
        // Load packages for comparison (max 4)
        $packages = Package::whereIn('id', $packageIds)
            ->take(4)
            ->get();
        
        // Get all packages for selection
        $allPackages = Package::where('status', 'active')
            ->orderBy('price', 'asc')
            ->get();
        
        return view($this->getViewPrefix() . '.comparison', compact('packages', 'allPackages'));
    }
}
