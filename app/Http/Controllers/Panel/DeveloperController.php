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
     */
    public function toggleTenancyStatus(Tenant $tenant): RedirectResponse
    {
        $tenant->status = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->save();

        return redirect()->back()
            ->with('success', "Tenancy status updated to {$tenant->status}.");
    }

    /**
     * Display subscription plans.
     */
    public function subscriptions(): View
    {
        // To be implemented with subscription model
        $subscriptions = [];

        return view('panels.developer.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Access any panel (select panel to access).
     */
    public function accessPanel(): View
    {
        $tenancies = Tenant::active()->get();

        return view('panels.developer.access-panel', compact('tenancies'));
    }

    /**
     * Search customers across all tenancies.
     */
    public function searchCustomers(Request $request): View
    {
        $query = $request->input('query');
        $customers = [];

        if ($query) {
            $customers = User::where('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->with(['tenant', 'roles'])
                ->paginate(20);
        }

        return view('panels.developer.customers.search', compact('customers', 'query'));
    }

    /**
     * View all customers across all tenancies.
     */
    public function allCustomers(): View
    {
        $customers = User::with(['tenant', 'roles'])
            ->latest()
            ->paginate(20);

        return view('panels.developer.customers.index', compact('customers'));
    }

    /**
     * Display audit logs.
     */
    public function auditLogs(): View
    {
        // To be implemented with audit log model
        $logs = [];

        return view('panels.developer.audit-logs', compact('logs'));
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
     */
    public function errorLogs(): View
    {
        // To be implemented with error log viewer
        return view('panels.developer.error-logs');
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
     */
    public function apiKeys(): View
    {
        return view('panels.developer.api-keys');
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
