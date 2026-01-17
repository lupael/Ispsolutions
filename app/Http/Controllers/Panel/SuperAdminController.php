<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NetworkUser;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
     */
    public function billingUserBase(): View
    {
        // Load user-based billing configuration
        return view('panels.super-admin.billing.user-base');
    }

    /**
     * Display panel-based billing configuration.
     */
    public function billingPanelBase(): View
    {
        // Load panel-based billing configuration
        return view('panels.super-admin.billing.panel-base');
    }

    /**
     * Display payment gateways listing.
     */
    public function paymentGatewayIndex(): View
    {
        // To be implemented with payment gateway model
        $gateways = [];

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
     */
    public function paymentGatewayStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
            'webhook_url' => 'nullable|url',
            'is_active' => 'required|boolean',
        ]);

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
        // To be implemented with SMS gateway model
        $gateways = [];

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
            'provider' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        // To be implemented with SMS gateway model
        // SmsGateway::create($validated);

        return redirect()->route('panel.super-admin.sms-gateway.index')
            ->with('success', 'SMS gateway added successfully.');
    }

    /**
     * Display logs.
     */
    public function logs(): View
    {
        // To be implemented with log viewer
        return view('panels.super-admin.logs');
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        return view('panels.super-admin.settings');
    }
}
