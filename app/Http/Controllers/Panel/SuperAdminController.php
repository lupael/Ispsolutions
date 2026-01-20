<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\PaymentGateway;
use App\Models\Role;
use App\Models\SmsGateway;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function dashboard(): View
    {
        // Exclude developer from user counts (super-admin can see other super-admins)
        $excludedRoleSlugs = ['developer'];
        
        $stats = [
            'total_users' => User::whereDoesntHave('roles', function ($query) use ($excludedRoleSlugs) {
                $query->whereIn('slug', $excludedRoleSlugs);
            })->count(),
            'total_network_users' => NetworkUser::count(),
            'active_users' => User::where('is_active', true)
                ->whereDoesntHave('roles', function ($query) use ($excludedRoleSlugs) {
                    $query->whereIn('slug', $excludedRoleSlugs);
                })->count(),
            'total_roles' => Role::whereNotIn('slug', $excludedRoleSlugs)->count(),
        ];

        return view('panels.super-admin.dashboard', compact('stats'));
    }

    /**
     * Display users listing.
     */
    public function users(): View
    {
        $users = User::with('roles')->latest()->paginate(20);

        return view('panels.super-admin.users.index', compact('users'));
    }

    /**
     * Display roles listing.
     */
    public function roles(): View
    {
        $roles = Role::withCount('users')->get();

        return view('panels.super-admin.roles.index', compact('roles'));
    }

    /**
     * Display ISP/Admin listing.
     */
    public function ispIndex(): View
    {
        $isps = Tenant::withCount('users')->latest()->paginate(20);

        return view('panels.super-admin.isp.index', compact('isps'));
    }

    /**
     * Show form to create a new ISP/Admin.
     */
    public function ispCreate(): View
    {
        return view('panels.super-admin.isp.create');
    }

    /**
     * Store a new ISP/Admin.
     */
    public function ispStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'database' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $tenant = Tenant::create($validated);

        return redirect()->route('panel.super-admin.isp.index')
            ->with('success', 'ISP/Admin created successfully.');
    }

    /**
     * Display fixed billing configuration.
     */
    public function billingFixed(): View
    {
        // Load billing configuration
        return view('panels.super-admin.billing.fixed');
    }

    /**
     * Display user-based billing configuration.
     */
    public function billingUserBase(): View
    {
        // Get billing configuration per user
        $subscriptions = \App\Models\Subscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_subscriptions' => \App\Models\Subscription::count(),
            'active_subscriptions' => \App\Models\Subscription::active()->count(),
            'total_revenue' => 0, // Calculate from subscription payments
            'monthly_recurring' => 0, // Calculate monthly recurring revenue
        ];

        return view('panels.super-admin.billing.user-base', compact('subscriptions', 'stats'));
    }

    /**
     * Display panel-based billing configuration.
     */
    public function billingPanelBase(): View
    {
        // Get billing configuration per panel/tenant
        $tenants = Tenant::withCount('users')
            ->with('subscription')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_users' => User::count(),
            'revenue_this_month' => 0, // Calculate from tenant subscriptions
        ];

        return view('panels.super-admin.billing.panel-base', compact('tenants', 'stats'));
    }

    /**
     * Display payment gateways listing.
     */
    public function paymentGatewayIndex(): View
    {
        $gateways = PaymentGateway::latest()->paginate(20);
        
        return view('panels.super-admin.payment-gateway.index', compact('gateways'));
    }

    /**
     * Show form to create a new payment gateway.
     */
    public function paymentGatewayCreate(): View
    {
        return view('panels.super-admin.payment-gateway.create');
    }

    /**
     * Store a new payment gateway.
     *
     * Note: webhook_url validation currently only checks URL format.
     * Consider adding additional validation to ensure the webhook URL
     * is either within the application's domain or on an allowlist to
     * prevent potential SSRF vulnerabilities or misconfiguration.
     */
    public function paymentGatewayStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug',
            'is_active' => 'sometimes|boolean',
            'test_mode' => 'sometimes|boolean',
            'configuration' => 'nullable|array',
        ]);

        // Validate that configuration contains required keys if provided
        if (isset($validated['configuration']) && is_array($validated['configuration'])) {
            $requiredConfigKeys = ['provider', 'api_key'];
            $missingConfigKeys = array_diff($requiredConfigKeys, array_keys($validated['configuration']));

            if (!empty($missingConfigKeys)) {
                return back()
                    ->withErrors([
                        'configuration' => 'The configuration must include the following keys: ' . implode(', ', $requiredConfigKeys) . '.',
                    ])
                    ->withInput();
            }
        }

        // The configuration field is automatically encrypted by the model cast
        try {
            PaymentGateway::create($validated);
        } catch (\Throwable $e) {
            // Log the underlying error and provide a user-friendly message
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'configuration' => 'The payment gateway could not be created due to an internal error. Please check the application configuration or contact support if the problem persists.',
                ]);
        }

        return redirect()->route('panel.super-admin.payment-gateway.index')
            ->with('success', 'Payment gateway added successfully.');
    }

    /**
     * Display SMS gateways listing.
     */
    public function smsGatewayIndex(): View
    {
        $gateways = SmsGateway::latest()->paginate(20);
        
        return view('panels.super-admin.sms-gateway.index', compact('gateways'));
    }

    /**
     * Show form to create a new SMS gateway.
     */
    public function smsGatewayCreate(): View
    {
        return view('panels.super-admin.sms-gateway.create');
    }

    /**
     * Store a new SMS gateway.
     */
    public function smsGatewayStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sms_gateways,slug',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
            'configuration' => 'nullable|array',
            'balance' => 'nullable|numeric|min:0',
            'rate_per_sms' => 'nullable|numeric|min:0',
        ]);

        // Validate that configuration contains required keys if provided
        if (isset($validated['configuration']) && is_array($validated['configuration'])) {
            $requiredConfigKeys = ['provider', 'api_key', 'sender_id'];
            $missingConfigKeys = array_diff($requiredConfigKeys, array_keys($validated['configuration']));

            if (!empty($missingConfigKeys)) {
                return back()
                    ->withErrors([
                        'configuration' => 'The configuration must include the following keys: ' . implode(', ', $requiredConfigKeys) . '.',
                    ])
                    ->withInput();
            }
        }

        // The configuration field is automatically encrypted by the model cast
        try {
            SmsGateway::create($validated);
        } catch (\Throwable $e) {
            // Log the underlying error and provide a user-friendly message
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'configuration' => 'The SMS gateway could not be created due to an internal error. Please check the application configuration or contact support if the problem persists.',
                ]);
        }

        return redirect()->route('panel.super-admin.sms-gateway.index')
            ->with('success', 'SMS gateway added successfully.');
    }

    /**
     * Display logs.
     */
    public function logs(): View
    {
        $logs = \App\Models\AuditLog::with(['user', 'auditable'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => \App\Models\AuditLog::count(),
            'today' => \App\Models\AuditLog::whereDate('created_at', today())->count(),
            'this_week' => \App\Models\AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => \App\Models\AuditLog::whereMonth('created_at', now()->month)->count(),
        ];

        return view('panels.super-admin.logs', compact('logs', 'stats'));
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        return view('panels.super-admin.settings');
    }
}
