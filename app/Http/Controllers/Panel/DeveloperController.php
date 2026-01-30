<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
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
     *
     * Automatically provisions a Super Admin account for the tenancy.
     * According to role hierarchy:
     * - Tenancy and Super Admin are effectively the same entity
     * - Creating a Super Admin without a tenancy is impossible
     * - Only Developers can create tenancies and Super Admins
     */
    public function storeTenancy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'database' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
            // Super Admin account fields
            'super_admin_name' => 'required|string|max:255',
            'super_admin_email' => 'required|email|unique:users,email',
            'super_admin_password' => 'required|string|min:8|confirmed',
        ]);

        \DB::transaction(function () use ($validated) {
            // Automatically provision Super Admin account first
            // Note: We create Super Admin first, then assign as tenant creator
            $superAdmin = User::create([
                'name' => $validated['super_admin_name'],
                'email' => $validated['super_admin_email'],
                'password' => bcrypt($validated['super_admin_password']),
                'tenant_id' => null, // Will be set after tenant creation
                'operator_level' => 10, // Super Admin level
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => auth()->id(), // Developer who initiated creation
            ]);

            // Create the tenancy with Super Admin as creator
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'] ?? null,
                'subdomain' => $validated['subdomain'] ?? null,
                'database' => $validated['database'] ?? null,
                'status' => $validated['status'],
                'created_by' => $superAdmin->id, // Super Admin is the tenant owner
            ]);

            // Update Super Admin to reference the tenant
            $superAdmin->update(['tenant_id' => $tenant->id]);

            // Assign Super Admin role
            $superAdminRole = \App\Models\Role::where('slug', 'super-admin')->first();
            if ($superAdminRole) {
                $superAdmin->roles()->attach($superAdminRole->id, ['tenant_id' => $tenant->id]);
            }
        });

        return redirect()->route('panel.developer.tenancies.index')
            ->with('success', 'Tenancy and Super Admin account created successfully.');
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
     */
    public function subscriptions(): View
    {
        $plans = \App\Models\SubscriptionPlan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('panels.developer.subscriptions.index', compact('plans'));
    }

    /**
     * Access any panel (select panel to access).
     */
    public function accessPanel(): View
    {
        // Get all tenancies for panel access selection
        $tenancies = Tenant::with('users')->where('status', 'active')->get();

        return view('panels.developer.access-panel', compact('tenancies'));
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
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $onlineCount = \App\Models\NetworkUserSession::where('status', 'active')
            ->whereNull('end_time')
            ->selectRaw('COUNT(DISTINCT user_id) as count')
            ->value('count') ?? 0;

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'active' => (int) ($statsData->active ?? 0),
            'online' => $onlineCount,
            'offline' => max(0, (int) ($statsData->active ?? 0) - $onlineCount),
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
            ->where('operator_level', 100) // Filter for customers only
            ->with(['tenant', 'roles'])
            ->latest()
            ->paginate(20);

        // Calculate stats for the view with a single aggregated query
        $statsData = User::allTenants()
            ->where('operator_level', 100) // Filter for customers only
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $onlineCount = \App\Models\NetworkUserSession::where('status', 'active')
            ->whereNull('end_time')
            ->selectRaw('COUNT(DISTINCT user_id) as count')
            ->value('count') ?? 0;

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'active' => (int) ($statsData->active ?? 0),
            'online' => $onlineCount,
            'offline' => max(0, (int) ($statsData->active ?? 0) - $onlineCount),
        ];

        return view('panels.developer.customers.index', compact('customers', 'query', 'stats'));
    }

    /**
     * Show customer details across all tenancies.
     */
    public function showCustomer(int $id): View
    {
        $customer = User::allTenants()
            ->with(['tenant', 'roles'])
            ->findOrFail($id);

        return view('panels.developer.customers.show', compact('customer'));
    }

    /**
     * Display audit logs.
     */
    public function auditLogs(): View
    {
        $logs = \App\Models\AuditLog::allTenants()
            ->with(['user', 'auditable'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => \App\Models\AuditLog::allTenants()->count(),
            'today' => \App\Models\AuditLog::allTenants()->whereDate('created_at', today())->count(),
            'this_week' => \App\Models\AuditLog::allTenants()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => \App\Models\AuditLog::allTenants()->whereMonth('created_at', now()->month)->count(),
        ];

        return view('panels.developer.audit-logs', compact('logs', 'stats'));
    }

    /**
     * Display system logs.
     */
    public function logs(): View
    {
        // Read Laravel log file
        $logFile = storage_path('logs/laravel.log');
        $logs = collect();
        $stats = [
            'info' => 0,
            'warning' => 0,
            'error' => 0,
            'debug' => 0,
            'total' => 0,
        ];

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);

            // Parse log entries (last 200 lines for performance)
            $recentLines = array_slice($lines, -200);
            $parsedLogs = [];

            foreach ($recentLines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                // Parse Laravel log format: [2024-01-19 12:00:00] environment.LEVEL: message
                if (preg_match('/\[(.*?)\]\s+\w+\.(INFO|WARNING|ERROR|DEBUG):\s+(.*)/', $line, $matches)) {
                    $level = strtolower($matches[2]);
                    $parsedLogs[] = [
                        'timestamp' => $matches[1] ?? now()->toDateTimeString(),
                        'level' => $level,
                        'message' => $matches[3] ?? $line,
                    ];
                    $stats[$level] = ($stats[$level] ?? 0) + 1;
                    $stats['total']++;
                }
            }

            $logs = collect(array_reverse($parsedLogs));
        }

        // Create paginator
        $page = request()->get('page', 1);
        $perPage = 20;
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs->forPage($page, $perPage),
            $logs->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.developer.logs', compact('logs', 'stats'));
    }

    /**
     * Display error logs.
     */
    public function errorLogs(): View
    {
        // Read Laravel log file
        $logFile = storage_path('logs/laravel.log');
        $logs = collect();

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);

            // Get last 100 error entries
            $errorLines = array_filter($lines, function ($line) {
                return str_contains($line, '[error]') || str_contains($line, 'ERROR') || str_contains($line, 'Exception');
            });

            $logs = collect(array_slice($errorLines, -100))->reverse();
        }

        return view('panels.developer.error-logs', compact('logs'));
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
        $apiKeys = \App\Models\ApiKey::allTenants()
            ->with('user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => \App\Models\ApiKey::allTenants()->count(),
            'active' => \App\Models\ApiKey::allTenants()->active()->count(),
            'expired' => \App\Models\ApiKey::allTenants()->where('expires_at', '<', now())->count(),
        ];

        return view('panels.developer.api-keys', compact('apiKeys', 'stats'));
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

    /**
     * Display Super Admin management.
     * Developer can see Super Admins across all tenants.
     */
    public function superAdmins(): View
    {
        $superAdmins = User::allTenants()->whereHas('roles', function ($q) {
            $q->where('slug', 'super-admin');
        })->with('tenant')->latest()->paginate(20);

        return view('panels.developer.super-admins.index', compact('superAdmins'));
    }

    /**
     * Show form to create Super Admin.
     */
    public function createSuperAdmin(): View
    {
        $tenants = Tenant::all();

        return view('panels.developer.super-admins.create', compact('tenants'));
    }

    /**
     * Display the specified Super Admin.
     */
    public function showSuperAdmin($id): View
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('slug', 'super-admin');
        })->with('roles', 'tenant')->findOrFail($id);

        return view('panels.developer.super-admins.show', compact('admin'));
    }

    /**
     * Show form to edit a Super Admin.
     */
    public function editSuperAdmin($id): View
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('slug', 'super-admin');
        })->with('roles', 'tenant')->findOrFail($id);

        $tenants = Tenant::all();

        return view('panels.developer.super-admins.edit', compact('admin', 'tenants'));
    }

    /**
     * Update the specified Super Admin.
     */
    public function updateSuperAdmin(Request $request, $id)
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('slug', 'super-admin');
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => ! empty($validated['password']) ? bcrypt($validated['password']) : $admin->password,
        ]);

        return redirect()->route('panel.developer.super-admins.index')
            ->with('success', 'Super Admin updated successfully.');
    }

    /**
     * Display payment gateways configuration.
     */
    public function paymentGateways(): View
    {
        $gateways = PaymentGateway::allTenants()
            ->latest()
            ->paginate(20);

        return view('panels.developer.gateways.payment', compact('gateways'));
    }

    /**
     * Display SMS gateways configuration.
     */
    public function smsGateways(): View
    {
        $gateways = \App\Models\SmsGateway::allTenants()
            ->latest()
            ->paginate(20);

        return view('panels.developer.gateways.sms', compact('gateways'));
    }

    /**
     * Display VPN pools configuration.
     */
    public function vpnPools(): View
    {
        $pools = \App\Models\VpnPool::with('vpnAccounts')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => \App\Models\VpnPool::count(),
            'active' => \App\Models\VpnPool::active()->count(),
            'total_ips' => \App\Models\VpnPool::sum('total_ips'),
            'used_ips' => \App\Models\VpnPool::sum('used_ips'),
        ];

        return view('panels.developer.vpn-pools', compact('pools', 'stats'));
    }

    /**
     * Display subscription plans management.
     */
    public function subscriptionPlans(): View
    {
        $plans = \App\Models\SubscriptionPlan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->paginate(20);

        $stats = [
            'total' => \App\Models\SubscriptionPlan::count(),
            'active' => \App\Models\SubscriptionPlan::active()->count(),
            'total_subscriptions' => \App\Models\Subscription::count(),
            'active_subscriptions' => \App\Models\Subscription::active()->count(),
        ];

        return view('panels.developer.subscriptions.index', compact('plans', 'stats'));
    }

    /**
     * Display all ISPs/Admins across tenants.
     */
    public function allAdmins(): View
    {
        $admins = User::allTenants()
            ->where('operator_level', 20)
            ->with('tenant')
            ->latest()
            ->paginate(20);

        return view('panels.developer.admins.index', compact('admins'));
    }

    /**
     * Create a new subscription plan.
     */
    public function createSubscription(): View
    {
        return view('panels.developer.subscriptions.create');
    }

    /**
     * Store a new subscription plan.
     */
    public function storeSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'max_users' => 'nullable|integer|min:0',
            'max_routers' => 'nullable|integer|min:0',
            'max_olts' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        \App\Models\SubscriptionPlan::create($validated);

        return redirect()->route('panel.developer.subscriptions.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Edit a subscription plan.
     */
    public function editSubscription($id): View
    {
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);
        return view('panels.developer.subscriptions.create', compact('plan'));
    }

    /**
     * Update a subscription plan.
     */
    public function updateSubscription(Request $request, $id): RedirectResponse
    {
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'max_users' => 'nullable|integer|min:0',
            'max_routers' => 'nullable|integer|min:0',
            'max_olts' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $plan->update($validated);

        return redirect()->route('panel.developer.subscriptions.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Delete a subscription plan.
     */
    public function deleteSubscription($id): RedirectResponse
    {
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return redirect()->route('panel.developer.subscriptions.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }

    /**
     * Create a payment gateway.
     */
    public function createPaymentGateway(): View
    {
        return view('panels.developer.gateways.payment-create');
    }

    /**
     * Store a payment gateway.
     */
    public function storePaymentGateway(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:stripe,paypal,razorpay,sslcommerz,bkash,nagad',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Map provider to slug column
        $validated['slug'] = $validated['provider'];
        unset($validated['provider']);

        // Set tenant_id if user has one
        $user = $request->user();
        if ($user && property_exists($user, 'tenant_id') && $user->tenant_id) {
            $validated['tenant_id'] = $user->tenant_id;
        }

        PaymentGateway::create($validated);

        return redirect()->route('panel.developer.gateways.payment')
            ->with('success', 'Payment gateway created successfully.');
    }

    /**
     * Edit a payment gateway.
     */
    public function editPaymentGateway($id): View
    {
        $gateway = PaymentGateway::findOrFail($id);
        return view('panels.developer.gateways.payment-create', compact('gateway'));
    }

    /**
     * Update a payment gateway.
     */
    public function updatePaymentGateway(Request $request, $id): RedirectResponse
    {
        $gateway = PaymentGateway::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:stripe,paypal,razorpay,sslcommerz,bkash,nagad',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Map provider to slug column
        $validated['slug'] = $validated['provider'];
        unset($validated['provider']);

        $gateway->update($validated);

        return redirect()->route('panel.developer.gateways.payment')
            ->with('success', 'Payment gateway updated successfully.');
    }

    /**
     * Delete a payment gateway.
     */
    public function deletePaymentGateway($id): RedirectResponse
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->delete();

        return redirect()->route('panel.developer.gateways.payment')
            ->with('success', 'Payment gateway deleted successfully.');
    }

    /**
     * Create an SMS gateway.
     */
    public function createSmsGateway(): View
    {
        return view('panels.developer.gateways.sms-create');
    }

    /**
     * Store an SMS gateway.
     */
    public function storeSmsGateway(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:twilio,nexmo,msg91,bulksms,custom',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Map provider to slug column
        $validated['slug'] = $validated['provider'];
        unset($validated['provider']);

        // Set tenant_id if user has one
        $user = $request->user();
        if ($user && property_exists($user, 'tenant_id') && $user->tenant_id) {
            $validated['tenant_id'] = $user->tenant_id;
        }

        \App\Models\SmsGateway::create($validated);

        return redirect()->route('panel.developer.gateways.sms')
            ->with('success', 'SMS gateway created successfully.');
    }

    /**
     * Edit an SMS gateway.
     */
    public function editSmsGateway($id): View
    {
        $gateway = \App\Models\SmsGateway::findOrFail($id);
        return view('panels.developer.gateways.sms-create', compact('gateway'));
    }

    /**
     * Update an SMS gateway.
     */
    public function updateSmsGateway(Request $request, $id): RedirectResponse
    {
        $gateway = \App\Models\SmsGateway::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:twilio,nexmo,msg91,bulksms,custom',
            'configuration' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Map provider to slug column
        $validated['slug'] = $validated['provider'];
        unset($validated['provider']);

        $gateway->update($validated);

        return redirect()->route('panel.developer.gateways.sms')
            ->with('success', 'SMS gateway updated successfully.');
    }

    /**
     * Delete an SMS gateway.
     */
    public function deleteSmsGateway($id): RedirectResponse
    {
        $gateway = \App\Models\SmsGateway::findOrFail($id);
        $gateway->delete();

        return redirect()->route('panel.developer.gateways.sms')
            ->with('success', 'SMS gateway deleted successfully.');
    }

    /**
     * Store a super admin.
     */
    public function storeSuperAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['operator_level'] = 10; // Super Admin level

        $user = User::create($validated);
        $user->assignRole('super-admin');

        return redirect()->route('panel.developer.super-admins.index')
            ->with('success', 'Super admin created successfully.');
    }

    /**
     * Edit a tenancy.
     */
    public function editTenancy($id): View
    {
        $tenancy = Tenant::findOrFail($id);
        return view('panels.developer.tenancies.edit', compact('tenancy'));
    }

    /**
     * Update a tenancy.
     */
    public function updateTenancy(Request $request, $id): RedirectResponse
    {
        $tenancy = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $id,
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $tenancy->update($validated);

        return redirect()->route('panel.developer.tenancies.index')
            ->with('success', 'Tenancy updated successfully.');
    }

    /**
     * Show an admin details.
     */
    public function showAdmin($id): View
    {
        $admin = User::allTenants()
            ->where('operator_level', 20)
            ->with(['tenant'])
            ->findOrFail($id);

        return view('panels.developer.admins.show', compact('admin'));
    }

    /**
     * Show role settings form.
     */
    public function showRoleSettings(): View
    {
        // Get current role names from all tenants (for display purposes)
        // In a multi-tenant system, this would typically be tenant-specific
        $roleNames = [
            'operator' => \App\Models\RoleLabelSetting::where('role_slug', 'operator')->value('custom_label') ?? 'Operator',
            'sub_operator' => \App\Models\RoleLabelSetting::where('role_slug', 'sub-operator')->value('custom_label') ?? 'Sub-Operator',
        ];

        return view('panels.developer.settings.roles', compact('roleNames'));
    }

    /**
     * Update role names.
     */
    public function updateRoleNames(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:50',
            'sub_operator_name' => 'required|string|max:50',
        ]);

        // Get the first tenant or use a global tenant_id = 1 for system-wide settings
        $tenantId = \App\Models\Tenant::first()->id ?? 1;

        // Update or create role labels
        \App\Models\RoleLabelSetting::setCustomLabel($tenantId, 'operator', $validated['operator_name']);
        \App\Models\RoleLabelSetting::setCustomLabel($tenantId, 'sub-operator', $validated['sub_operator_name']);

        return redirect()->back()->with('success', 'Role names updated successfully.');
    }

    /**
     * Show subscription features configuration form.
     */
    public function showSubscriptionFeatures(): View
    {
        // Get current features and limits from settings
        $features = \App\Models\Setting::get('subscription_features', []);
        $limits = \App\Models\Setting::get('subscription_limits', [
            'max_admins' => 5,
            'max_users_per_panel' => 1000,
            'max_routers' => 10,
            'max_olts' => 5,
        ]);

        return view('panels.developer.subscriptions.features', compact('features', 'limits'));
    }

    /**
     * Update subscription features.
     */
    public function updateSubscriptionFeatures(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'features' => 'array',
            'features.*' => 'in:mikrotik,olt,billing,sms,reports,api',
        ]);

        \App\Models\Setting::set('subscription_features', $validated['features'] ?? []);

        return redirect()->back()->with('success', 'Subscription features updated successfully.');
    }

    /**
     * Update subscription limits.
     */
    public function updateSubscriptionLimits(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_admins' => 'required|integer|min:1',
            'max_users_per_panel' => 'required|integer|min:1',
            'max_routers' => 'required|integer|min:1',
            'max_olts' => 'required|integer|min:1',
        ]);

        \App\Models\Setting::set('subscription_limits', $validated);

        return redirect()->back()->with('success', 'Subscription limits updated successfully.');
    }
}
