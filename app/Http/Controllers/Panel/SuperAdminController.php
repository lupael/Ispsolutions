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
use Illuminate\Support\Facades\DB;
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
     * Excludes developers as super-admin should not see or manage developer accounts.
     * Super Admin can only see users in tenants they created.
     */
    public function users(): View
    {
        $superAdmin = auth()->user();
        
        $users = User::with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('slug', 'developer');
            })
            ->where('is_subscriber', false) // Exclude customers (subscribers)
            ->where(function ($query) use ($superAdmin) {
                // Show users in tenants created by this Super Admin
                $query->whereIn('tenant_id', function ($q) use ($superAdmin) {
                    $q->select('id')
                        ->from('tenants')
                        ->where('created_by', $superAdmin->id);
                })
                // Also show the Super Admin themselves
                ->orWhere('id', $superAdmin->id);
            })
            ->latest()
            ->paginate(20);

        return view('panels.super-admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function usersCreate(): View
    {
        $roles = Role::where('slug', '!=', 'developer')->get();

        return view('panels.super-admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function usersStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Get the role to check its level
        $role = \App\Models\Role::findOrFail($validated['role_id']);
        
        // Authorization check: Super Admin can only create Admins (level 20)
        if (!auth()->user()->canCreateUserWithLevel($role->level)) {
            abort(403, 'You are not authorized to create users with this role.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'tenant_id' => auth()->user()->tenant_id, // Enforce tenant isolation
            'operator_level' => $role->level,
            'created_by' => auth()->id(),
        ]);

        $user->roles()->attach($validated['role_id'], [
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return redirect()->route('panel.super-admin.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function usersEdit($id): View
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::where('slug', '!=', 'developer')->get();

        return view('panels.super-admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function usersUpdate(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => ! empty($validated['password']) ? bcrypt($validated['password']) : $user->password,
        ]);

        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('panel.super-admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function usersDestroy($id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Prevent deletion of users with developer role
        if ($user->hasRole('developer')) {
            return redirect()->route('panel.super-admin.users')
                ->with('error', 'Cannot delete developer users.');
        }

        $user->delete();

        return redirect()->route('panel.super-admin.users')
            ->with('success', 'User deleted successfully.');
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
     *
     * Automatically provisions an Admin account for the ISP.
     * According to role hierarchy:
     * - Each ISP represents a tenant segment managed by an Admin
     * - When a Super Admin creates a new ISP, an Admin account must be automatically provisioned
     * - Each Admin represents multiple Operators
     */
    public function ispStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'database' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            // Admin account fields
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $result = \DB::transaction(function () use ($validated) {
            $superAdmin = auth()->user();

            // Create the ISP tenant (using current Super Admin as creator)
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'] ?? null,
                'subdomain' => $validated['subdomain'] ?? null,
                'database' => $validated['database'] ?? null,
                'status' => $validated['status'],
                'created_by' => $superAdmin->id, // Super Admin who created it
            ]);

            // Automatically provision Admin account
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => bcrypt($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'operator_level' => 20, // Admin level
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $superAdmin->id,
            ]);

            // Assign Admin role
            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $admin->roles()->attach($adminRole->id, ['tenant_id' => $tenant->id]);
            }

            return ['tenant' => $tenant, 'admin' => $admin];
        });

        return redirect()->route('panel.super-admin.isp.index')
            ->with('success', 'ISP and Admin account created successfully.');
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
            'slug' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'test_mode' => 'sometimes|boolean',
            'configuration' => 'nullable|array',
        ]);

        // Convert checkbox values to boolean
        $validated['is_active'] = $request->has('is_active');
        $validated['test_mode'] = $request->has('test_mode');

        // The configuration field is automatically encrypted by the model cast
        try {
            // Check if gateway already exists, update or create
            $gateway = PaymentGateway::where('slug', $validated['slug'])->first();

            if ($gateway) {
                $gateway->update($validated);
                $message = 'Payment gateway updated successfully.';
            } else {
                PaymentGateway::create($validated);
                $message = 'Payment gateway added successfully.';
            }
        } catch (\Throwable $e) {
            // Log the underlying error and provide a user-friendly message
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'configuration' => 'The payment gateway could not be saved due to an internal error. Please check the application configuration or contact support if the problem persists.',
                ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Show payment gateway settings page
     */
    public function paymentGatewaySettings(): View
    {
        // Get all gateways indexed by slug
        $gateways = [
            'bkash' => PaymentGateway::where('slug', 'bkash')->first(),
            'nagad' => PaymentGateway::where('slug', 'nagad')->first(),
            'sslcommerz' => PaymentGateway::where('slug', 'sslcommerz')->first(),
            'stripe' => PaymentGateway::where('slug', 'stripe')->first(),
        ];

        return view('panels.super-admin.payment-gateway.settings', compact('gateways'));
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

            if (! empty($missingConfigKeys)) {
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

    /**
     * Store billing fixed configuration.
     */
    public function billingFixedStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_fee' => 'required|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'currency' => 'required|string|in:USD,EUR,BDT,INR',
            'billing_cycle' => 'required|string|in:monthly,quarterly,yearly',
            'auto_renew' => 'nullable|boolean',
        ]);

        // Store configuration (you may want to use a settings table or config)
        foreach ($validated as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['key' => 'billing_fixed_' . $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return redirect()->route('panel.super-admin.billing.fixed')
            ->with('success', 'Fixed billing configuration saved successfully.');
    }

    /**
     * Store billing user-base configuration.
     */
    public function billingUserBaseStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'price_per_user' => 'required|numeric|min:0',
            'minimum_users' => 'nullable|integer|min:0',
            'currency' => 'required|string|in:USD,EUR,BDT,INR',
        ]);

        foreach ($validated as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['key' => 'billing_user_base_' . $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return redirect()->route('panel.super-admin.billing.user-base')
            ->with('success', 'User-base billing configuration saved successfully.');
    }

    /**
     * Store billing panel-base configuration.
     */
    public function billingPanelBaseStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'price_per_panel' => 'required|numeric|min:0',
            'currency' => 'required|string|in:USD,EUR,BDT,INR',
        ]);

        foreach ($validated as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                ['key' => 'billing_panel_base_' . $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return redirect()->route('panel.super-admin.billing.panel-base')
            ->with('success', 'Panel-base billing configuration saved successfully.');
    }

    /**
     * Edit an ISP.
     */
    public function ispEdit($id): View
    {
        $isp = User::where('operator_level', 20)->findOrFail($id);
        return view('panels.super-admin.isp.edit', compact('isp'));
    }

    /**
     * Update an ISP admin profile.
     */
    public function ispUpdate(Request $request, $id): RedirectResponse
    {
        $isp = User::where('operator_level', 20)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $isp->update($validated);

        return redirect()->route('panel.super-admin.isp.index')
            ->with('success', 'ISP admin profile updated successfully.');
    }

    /**
     * Edit a payment gateway.
     */
    public function paymentGatewayEdit($id): View
    {
        $gateway = \App\Models\PaymentGateway::findOrFail($id);
        return view('panels.super-admin.payment-gateway.create', compact('gateway'));
    }

    /**
     * Update a payment gateway.
     */
    public function paymentGatewayUpdate(Request $request, $id): RedirectResponse
    {
        $gateway = \App\Models\PaymentGateway::findOrFail($id);

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

        return redirect()->route('panel.super-admin.payment-gateway.index')
            ->with('success', 'Payment gateway updated successfully.');
    }

    /**
     * Delete a payment gateway.
     */
    public function paymentGatewayDestroy($id): RedirectResponse
    {
        $gateway = \App\Models\PaymentGateway::findOrFail($id);
        $gateway->delete();

        return redirect()->route('panel.super-admin.payment-gateway.index')
            ->with('success', 'Payment gateway deleted successfully.');
    }

    /**
     * Edit an SMS gateway.
     */
    public function smsGatewayEdit($id): View
    {
        $gateway = \App\Models\SmsGateway::findOrFail($id);
        return view('panels.super-admin.sms-gateway.create', compact('gateway'));
    }

    /**
     * Update an SMS gateway.
     */
    public function smsGatewayUpdate(Request $request, $id): RedirectResponse
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

        return redirect()->route('panel.super-admin.sms-gateway.index')
            ->with('success', 'SMS gateway updated successfully.');
    }

    /**
     * Delete an SMS gateway.
     */
    public function smsGatewayDestroy($id): RedirectResponse
    {
        $gateway = \App\Models\SmsGateway::findOrFail($id);
        $gateway->delete();

        return redirect()->route('panel.super-admin.sms-gateway.index')
            ->with('success', 'SMS gateway deleted successfully.');
    }
}
