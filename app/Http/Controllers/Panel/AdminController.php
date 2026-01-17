<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NetworkUser;
use App\Models\ServicePackage;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Models\CiscoDevice;
use App\Models\Olt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_network_users' => NetworkUser::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_packages' => ServicePackage::count(),
            'total_mikrotik' => MikrotikRouter::count(),
            'total_nas' => Nas::count(),
            'total_cisco' => CiscoDevice::count(),
            'total_olt' => Olt::count(),
        ];

        return view('panels.admin.dashboard', compact('stats'));
    }

    /**
     * Display users listing.
     */
    public function users(): View
    {
        $users = User::with('roles')->latest()->paginate(20);

        return view('panels.admin.users.index', compact('users'));
    }

    /**
     * Display network users listing.
     */
    public function networkUsers(): View
    {
        $networkUsers = NetworkUser::latest()->paginate(20);

        return view('panels.admin.network-users.index', compact('networkUsers'));
    }

    /**
     * Display packages listing.
     */
    public function packages(): View
    {
        $packages = ServicePackage::get();

        return view('panels.admin.packages.index', compact('packages'));
    }

    /**
     * Display settings.
     */
    public function settings(): View
    {
        return view('panels.admin.settings');
    }

    /**
     * Display MikroTik routers listing.
     * 
     * Displays paginated list of MikroTik routers for admin users with full management access.
     * Includes 20 items per page with tenant isolation automatically applied via BelongsToTenant trait.
     */
    public function mikrotikRouters(): View
    {
        $routers = MikrotikRouter::latest()->paginate(20);

        return view('panels.admin.mikrotik.index', compact('routers'));
    }

    /**
     * Display NAS devices listing.
     * 
     * Displays paginated list of Network Access Server devices for admin users with full management access.
     * Includes 20 items per page with tenant isolation automatically applied via BelongsToTenant trait.
     */
    public function nasDevices(): View
    {
        $devices = Nas::latest()->paginate(20);

        return view('panels.admin.nas.index', compact('devices'));
    }

    /**
     * Display Cisco devices listing.
     * 
     * Displays paginated list of Cisco network devices for admin users with full management access.
     * Includes 20 items per page with tenant isolation automatically applied via BelongsToTenant trait.
     */
    public function ciscoDevices(): View
    {
        $devices = CiscoDevice::latest()->paginate(20);

        return view('panels.admin.cisco.index', compact('devices'));
    }

    /**
     * Display OLT devices listing.
     * 
     * Displays paginated list of Optical Line Terminal devices for admin users with full management access.
     * Includes 20 items per page with tenant isolation automatically applied via BelongsToTenant trait.
     */
    public function oltDevices(): View
    {
        $devices = Olt::latest()->paginate(20);

        return view('panels.admin.olt.index', compact('devices'));
    }

    /**
     * Display customers listing.
     */
    public function customers(): View
    {
        $customers = NetworkUser::with('package')->latest()->paginate(20);
        $packages = ServicePackage::all();
        
        $stats = [
            'total' => NetworkUser::count(),
            'active' => NetworkUser::where('status', 'active')->count(),
            'online' => 0,
            'offline' => NetworkUser::count(),
        ];

        return view('panels.admin.customers.index', compact('customers', 'packages', 'stats'));
    }

    /**
     * Show customer create form.
     */
    public function customersCreate(): View
    {
        $packages = ServicePackage::all();

        return view('panels.admin.customers.create', compact('packages'));
    }

    /**
     * Show customer edit form.
     */
    public function customersEdit($id): View
    {
        $customer = NetworkUser::with('package')->findOrFail($id);
        $packages = ServicePackage::all();

        return view('panels.admin.customers.edit', compact('customer', 'packages'));
    }

    /**
     * Show customer detail.
     */
    public function customersShow($id): View
    {
        $customer = NetworkUser::with('package', 'sessions')->findOrFail($id);

        return view('panels.admin.customers.show', compact('customer'));
    }

    /**
     * Display deleted customers.
     */
    public function deletedCustomers(): View
    {
        $customers = collect();

        return view('panels.admin.customers.deleted', compact('customers'));
    }

    /**
     * Display online customers.
     */
    public function onlineCustomers(): View
    {
        $customers = NetworkUser::with('package')->where('status', 'active')->latest()->paginate(20);
        
        $stats = [
            'online' => $customers->total(),
            'sessions' => 0,
        ];

        return view('panels.admin.customers.online', compact('customers', 'stats'));
    }

    /**
     * Display offline customers.
     */
    public function offlineCustomers(): View
    {
        $customers = NetworkUser::with('package')->latest()->paginate(20);

        return view('panels.admin.customers.offline', compact('customers'));
    }

    /**
     * Display customer import requests.
     */
    public function customerImportRequests(): View
    {
        $importRequests = collect();

        return view('panels.admin.customers.import-requests', compact('importRequests'));
    }

    /**
     * Show PPPoE customer import form.
     */
    public function pppoeCustomerImport(): View
    {
        $routers = MikrotikRouter::all();
        $packages = ServicePackage::all();

        return view('panels.admin.customers.pppoe-import', compact('routers', 'packages'));
    }

    /**
     * Show bulk update form.
     */
    public function bulkUpdateUsers(): View
    {
        $packages = ServicePackage::all();

        return view('panels.admin.customers.bulk-update', compact('packages'));
    }

    /**
     * Display account transactions.
     */
    public function accountTransactions(): View
    {
        return view('panels.admin.accounting.transactions');
    }

    /**
     * Display payment gateway transactions.
     */
    public function paymentGatewayTransactions(): View
    {
        return view('panels.admin.accounting.payment-gateway-transactions');
    }

    /**
     * Display account statement.
     */
    public function accountStatement(): View
    {
        return view('panels.admin.accounting.statement');
    }

    /**
     * Display accounts payable.
     */
    public function accountsPayable(): View
    {
        return view('panels.admin.accounting.payable');
    }

    /**
     * Display accounts receivable.
     */
    public function accountsReceivable(): View
    {
        return view('panels.admin.accounting.receivable');
    }

    /**
     * Display income vs expense report.
     */
    public function incomeExpenseReport(): View
    {
        return view('panels.admin.accounting.income-expense-report');
    }

    /**
     * Display expense report.
     */
    public function expenseReport(): View
    {
        return view('panels.admin.accounting.expense-report');
    }

    /**
     * Display expenses management.
     */
    public function expenses(): View
    {
        return view('panels.admin.accounting.expenses');
    }

    /**
     * Display VAT collections.
     */
    public function vatCollections(): View
    {
        return view('panels.admin.accounting.vat-collections');
    }

    /**
     * Display customer payments.
     */
    public function customerPayments(): View
    {
        return view('panels.admin.accounting.customer-payments');
    }

    /**
     * Display gateway customer payments.
     */
    public function gatewayCustomerPayments(): View
    {
        return view('panels.admin.accounting.gateway-customer-payments');
    }

    /**
     * Display operators listing.
     */
    public function operators(): View
    {
        $operators = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn('slug', ['manager', 'staff', 'reseller', 'sub-reseller']);
            })
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['manager', 'staff', 'reseller', 'sub-reseller']);
            })->count(),
            'active' => User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['manager', 'staff', 'reseller', 'sub-reseller']);
            })->where('is_active', true)->count(),
            'managers' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'manager');
            })->count(),
            'staff' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })->count(),
        ];

        return view('panels.admin.operators.index', compact('operators', 'stats'));
    }

    /**
     * Show create operator form.
     */
    public function operatorsCreate(): View
    {
        return view('panels.admin.operators.create');
    }

    /**
     * Show edit operator form.
     */
    public function operatorsEdit($id): View
    {
        $operator = User::with('roles')->findOrFail($id);

        return view('panels.admin.operators.edit', compact('operator'));
    }

    /**
     * Display sub-operators hierarchy.
     */
    public function subOperators(): View
    {
        $hierarchy = User::with(['roles', 'subordinates.roles'])
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'manager');
            })
            ->get();

        $stats = [
            'supervisors' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'manager');
            })->count(),
            'subordinates' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })->count(),
            'avg_team_size' => 0,
        ];

        return view('panels.admin.operators.sub-operators', compact('hierarchy', 'stats'));
    }

    /**
     * Display staff members.
     */
    public function staff(): View
    {
        $staff = User::with(['roles', 'supervisor'])
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })->count(),
            'active' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })->where('is_active', true)->count(),
            'on_duty' => 0,
            'departments' => 4,
        ];

        return view('panels.admin.operators.staff', compact('staff', 'stats'));
    }

    /**
     * Display operator profile.
     */
    public function operatorProfile($id): View
    {
        $operator = User::with(['roles', 'supervisor'])->findOrFail($id);

        $stats = [
            'customers_created' => 0,
            'tickets_resolved' => 0,
            'total_logins' => 0,
            'days_active' => $operator->created_at->diffInDays(now()),
        ];

        return view('panels.admin.operators.profile', compact('operator', 'stats'));
    }

    /**
     * Manage operator special permissions.
     */
    public function operatorSpecialPermissions($id): View
    {
        $operator = User::with('roles')->findOrFail($id);

        return view('panels.admin.operators.special-permissions', compact('operator'));
    }
}
