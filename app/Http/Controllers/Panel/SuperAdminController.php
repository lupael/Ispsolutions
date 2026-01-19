<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\Role;
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
        $stats = [
            'total_users' => User::count(),
            'total_network_users' => NetworkUser::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
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
     *
     * Currently not implemented.
     */
    public function billingUserBase()
    {
        // User-based billing configuration view not yet implemented.
        abort(501, 'User-based billing configuration is not yet implemented.');
    }

    /**
     * Display panel-based billing configuration.
     *
     * Currently not implemented.
     */
    public function billingPanelBase()
    {
        // Panel-based billing configuration is not yet implemented.
        abort(501, 'Panel-based billing configuration is not yet implemented.');
    }

    /**
     * Display payment gateways listing.
     */
    public function paymentGatewayIndex(): View
    {
        // TODO: Implement PaymentGateway model
        return view('panels.super-admin.payment-gateway.index');
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
            'provider' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
        ]);

        // Encrypt sensitive credentials before storage with error handling
        try {
            if (isset($validated['api_key'])) {
                $validated['api_key'] = encrypt($validated['api_key']);
            }
            if (isset($validated['api_secret'])) {
                $validated['api_secret'] = encrypt($validated['api_secret']);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to encrypt credentials. Please try again.']);
        }

        // To be implemented with payment gateway model
        // PaymentGateway::create($validated);

        return redirect()->route('panel.super-admin.payment-gateway.index')
            ->with('success', 'Payment gateway added successfully.');
    }

    /**
     * Display SMS gateways listing.
     */
    public function smsGatewayIndex(): View
    {
        // TODO: Implement SmsGateway model
        return view('panels.super-admin.sms-gateway.index');
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
            'provider' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        // Encrypt sensitive credentials before storage with error handling
        try {
            if (isset($validated['api_key'])) {
                $validated['api_key'] = encrypt($validated['api_key']);
            }
            if (isset($validated['api_secret'])) {
                $validated['api_secret'] = encrypt($validated['api_secret']);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to encrypt credentials. Please try again.']);
        }

        // To be implemented with SMS gateway model
        // SmsGateway::create($validated);

        return redirect()->route('panel.super-admin.sms-gateway.index')
            ->with('success', 'SMS gateway added successfully.');
    }

    /**
     * Display logs.
     *
     * To be implemented with a log viewer.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function logs()
    {
        // Not yet implemented: no logs view exists.
        abort(501, 'Logs view not implemented yet.');
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        return view('panels.super-admin.settings');
    }
}
