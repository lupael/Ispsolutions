<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    /**
     * Display the developer dashboard.
     */
    public function dashboard(): View
    {
        // Get system statistics
        $systemStats = $this->getSystemStats();

        // Get ISP statistics
        $stats = [
            'total_tenancies' => Tenant::count(),
            'active_tenancies' => Tenant::where('status', 'active')->count(),
            'total_users' => User::count(),
            'api_calls_today' => 0, // To be implemented
            'total_endpoints' => 0, // To be implemented
            'system_health' => 'Healthy',

            // ISP Statistics (reusing active_tenancies for total_isp)
            'ppp_users' => \App\Models\MikrotikPppoeUser::count(),
            'hotspot_users' => \App\Models\HotspotUser::count(),
            'total_routers' => \App\Models\MikrotikRouter::count(),
            'total_olts' => \App\Models\Olt::count(),
        ];

        return view('panels.developer.dashboard', compact('stats', 'systemStats'));
    }

    /**
     * Get system statistics (RAM, CPU, HDD, etc.)
     */
    private function getSystemStats(): array
    {
        $stats = [
            'ram' => [
                'total' => 0,
                'used' => 0,
                'free' => 0,
                'percentage' => 0,
            ],
            'disk' => [
                'total' => 0,
                'used' => 0,
                'free' => 0,
                'percentage' => 0,
            ],
            'cpu' => [
                'cores' => 0,
                'load_1' => 0,
                'load_5' => 0,
                'load_15' => 0,
            ],
        ];

        // Try to get actual system stats (Linux only)
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                // Get RAM info
                if (file_exists('/proc/meminfo')) {
                    $meminfo = file_get_contents('/proc/meminfo');
                    preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
                    preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

                    if (! empty($total[1]) && ! empty($available[1])) {
                        $stats['ram']['total'] = round($total[1] / 1024 / 1024, 2); // Convert to GB
                        $stats['ram']['free'] = round($available[1] / 1024 / 1024, 2);
                        $stats['ram']['used'] = round($stats['ram']['total'] - $stats['ram']['free'], 2);
                        $stats['ram']['percentage'] = $stats['ram']['total'] > 0
                            ? round(($stats['ram']['used'] / $stats['ram']['total']) * 100, 1)
                            : 0;
                    }
                }

                // Get disk info
                $diskTotal = disk_total_space('/');
                $diskFree = disk_free_space('/');
                if ($diskTotal && $diskFree) {
                    $stats['disk']['total'] = round($diskTotal / 1024 / 1024 / 1024, 2); // Convert to GB
                    $stats['disk']['free'] = round($diskFree / 1024 / 1024 / 1024, 2);
                    $stats['disk']['used'] = round($stats['disk']['total'] - $stats['disk']['free'], 2);
                    $stats['disk']['percentage'] = $stats['disk']['total'] > 0
                        ? round(($stats['disk']['used'] / $stats['disk']['total']) * 100, 1)
                        : 0;
                }

                // Get CPU info
                if (file_exists('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    preg_match_all('/^processor/m', $cpuinfo, $matches);
                    $stats['cpu']['cores'] = count($matches[0]);
                }

                // Get load average
                if (function_exists('sys_getloadavg')) {
                    $load = sys_getloadavg();
                    $stats['cpu']['load_1'] = round($load[0], 2);
                    $stats['cpu']['load_5'] = round($load[1], 2);
                    $stats['cpu']['load_15'] = round($load[2], 2);
                }
            } catch (\Exception $e) {
                // Silent fail - return default values
            }
        }

        return $stats;
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

        if ($query) {
            // Escape special LIKE characters to prevent unintended wildcard matching
            $escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

            $customers = User::allTenants()
                ->where(function ($q) use ($escapedQuery) {
                    $q->where('email', 'like', "%{$escapedQuery}%")
                        ->orWhere('name', 'like', "%{$escapedQuery}%");
                })
                ->with(['tenant', 'roles'])
                ->paginate(20);
        } else {
            // When no query is provided, show all customers (consistent with allCustomers())
            $customers = User::allTenants()
                ->with(['tenant', 'roles'])
                ->latest()
                ->paginate(20);
        }

        // Calculate stats for the view with a single aggregated query
        $statsData = User::allTenants()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'active' => (int) ($statsData->active ?? 0),
            'online' => 0, // TODO: Implement online user tracking
            'offline' => 0, // TODO: Implement offline user tracking
        ];

        return view('panels.developer.customers.index', compact('customers', 'query', 'stats'));
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

        // Calculate stats for the view with a single aggregated query
        $statsData = User::allTenants()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'active' => (int) ($statsData->active ?? 0),
            'online' => 0, // TODO: Implement online user tracking
            'offline' => 0, // TODO: Implement offline user tracking
        ];

        return view('panels.developer.customers.index', compact('customers', 'query', 'stats'));
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
        // TODO: Implement proper log model and viewer
        // For now, return empty paginated collection to prevent blade errors
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stats = [
            'info' => 0,
            'warning' => 0,
            'error' => 0,
            'debug' => 0,
            'total' => 0,
        ];

        return view('panels.developer.logs', compact('logs', 'stats'));
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
