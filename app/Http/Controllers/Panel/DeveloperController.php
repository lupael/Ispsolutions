<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DeveloperController extends Controller
{
    /**
     * Display the developer dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'total_tenancies' => Tenant::count(),
            'active_tenancies' => Tenant::where('status', 'active')->count(),
            'total_users' => User::count(),
            'api_calls_today' => 0, // To be implemented
            'system_health' => 'Healthy',
        ];

        return view('panels.developer.dashboard', compact('stats'));
    }

    /**
     * Display all tenancies.
     */
    public function tenancies(): View
    {
        $tenancies = Tenant::withCount('users')->latest()->paginate(20);

        return view('panels.developer.tenancies.index', compact('tenancies'));
    }

    /**
     * Show form to create a new tenancy (Super Admin/ISP).
     */
    public function createTenancy(): View
    {
        return view('panels.developer.tenancies.create');
    }

    /**
     * Store a new tenancy.
     */
    public function storeTenancy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'database' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $tenant = Tenant::create($validated);

        return redirect()->route('panel.developer.tenancies.index')
            ->with('success', 'Tenancy created successfully.');
    }

    /**
     * Suspend or activate a tenancy.
     *
     * Note: This method allows any Developer to suspend or activate any tenancy.
     * Consider adding explicit authorization checks and comprehensive audit logging
     * for this sensitive operation that could impact all users in a tenancy.
     */
    public function toggleTenancyStatus(Tenant $tenancy): RedirectResponse
    {
        $tenancy->status = $tenancy->status === 'active' ? 'suspended' : 'active';
        $tenancy->save();

        return redirect()->back()
            ->with('success', "Tenancy status updated to {$tenancy->status}.");
    }

    /**
     * Display subscription plans.
     *
     * Currently not implemented.
     */
    public function subscriptions()
    {
        // To be implemented with subscription model and corresponding view.
        abort(501, 'Subscriptions feature is not implemented yet.');
    }

    /**
     * Access any panel (select panel to access).
     *
     * Currently not implemented.
     */
    public function accessPanel()
    {
        // Feature not yet implemented: the corresponding view does not exist.
        // Once implemented, replace this with a valid view() call.
        abort(501, 'Access panel feature is not yet implemented.');
    }

    /**
     * Search customers across all tenancies.
     */
    public function searchCustomers(Request $request): View
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $query = $request->input('query');
        $customers = [];

        if ($query) {
            // Escape special LIKE characters to prevent unintended wildcard matching
            $escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
            
            $customers = User::allTenants()
                ->where(function($q) use ($escapedQuery) {
                    $q->where('email', 'like', "%{$escapedQuery}%")
                      ->orWhere('name', 'like', "%{$escapedQuery}%");
                })
                ->with(['tenant', 'roles'])
                ->paginate(20);
        }

        return view('panels.developer.customers.index', compact('customers', 'query'));
    }

    /**
     * View all customers across all tenancies.
     */
    public function allCustomers(): View
    {
        $query = null;

        $customers = User::allTenants()
            ->with(['tenant', 'roles'])
            ->latest()
            ->paginate(20);

        return view('panels.developer.customers.index', compact('customers', 'query'));
    }

    /**
     * Display audit logs.
     *
     * Currently not implemented.
     */
    public function auditLogs()
    {
        // Not yet implemented: audit log model and view will be added later.
        abort(501, 'Audit logs view is not yet implemented.');
    }

    /**
     * Display system logs.
     */
    public function logs(): View
    {
        // To be implemented with log viewer
        return view('panels.developer.logs');
    }

    /**
     * Display error logs.
     *
     * Currently not implemented.
     */
    public function errorLogs()
    {
        // Not yet implemented: error log viewer
        abort(501, 'Error logs view is not yet implemented.');
    }

    /**
     * Display API documentation.
     */
    public function apiDocs(): View
    {
        return view('panels.developer.api-docs');
    }

    /**
     * Manage API keys.
     *
     * Currently not implemented.
     */
    public function apiKeys()
    {
        abort(501, 'API key management is not yet implemented.');
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        return view('panels.developer.settings');
    }

    /**
     * Display debugging tools.
     */
    public function debug(): View
    {
        return view('panels.developer.debug');
    }
}
