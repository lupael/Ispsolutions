<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CiscoDevice;
use App\Models\DeviceMonitor;
use App\Models\IpAllocation;
use App\Models\IpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Models\NetworkUser;
use App\Models\Olt;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\RadAcct;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        // Exclude developer and super-admin from user counts
        $excludedRoleSlugs = ['developer', 'super-admin'];
        
        $stats = [
            'total_users' => User::whereDoesntHave('roles', function ($query) use ($excludedRoleSlugs) {
                $query->whereIn('slug', $excludedRoleSlugs);
            })->count(),
            'total_network_users' => NetworkUser::count(),
            'active_users' => User::where('is_active', true)
                ->whereDoesntHave('roles', function ($query) use ($excludedRoleSlugs) {
                    $query->whereIn('slug', $excludedRoleSlugs);
                })->count(),
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
        $packages = ServicePackage::paginate(20);

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
     * @deprecated This is a legacy route. Use routers() method at panel.admin.network.routers instead.
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
    /**
     * Display OLT devices listing.
     *
     * @deprecated This is a legacy route. Use oltList() method at panel.admin.network.olt instead.
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
        // TODO: Implement soft delete functionality for customers
        // For now, return empty paginated collection to prevent blade errors
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

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
        // TODO: Implement customer import request tracking
        // For now, return empty paginated collection to prevent blade errors
        $importRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

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

    /**
     * Display payment gateways listing.
     */
    public function paymentGateways(): View
    {
        $gateways = PaymentGateway::latest()->paginate(20);

        $totalPayments = Payment::whereNotNull('payment_gateway_id')->count();
        $completedPayments = Payment::whereNotNull('payment_gateway_id')
            ->where('status', 'completed')
            ->count();

        $stats = [
            'active' => PaymentGateway::where('is_active', true)->count(),
            'total_transactions' => $totalPayments,
            'success_rate' => $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 2) : 0,
            'total_amount' => Payment::whereNotNull('payment_gateway_id')
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('panels.admin.payment-gateways.index', compact('gateways', 'stats'));
    }

    /**
     * Show payment gateway create form.
     */
    public function paymentGatewaysCreate(): View
    {
        return view('panels.admin.payment-gateways.create');
    }

    /**
     * Display network routers listing.
     */
    public function routers(): View
    {
        $routers = MikrotikRouter::with('networkUsers')->paginate(20);

        $stats = [
            'total' => MikrotikRouter::count(),
            'online' => MikrotikRouter::where('status', 'online')->count(),
            'offline' => MikrotikRouter::where('status', 'offline')->count(),
            'warning' => 0,
        ];

        return view('panels.admin.network.routers', compact('routers', 'stats'));
    }

    /**
     * Show create router form.
     */
    public function routersCreate(): View
    {
        return view('panels.admin.network.routers-create');
    }

    /**
     * Display OLT devices listing.
     */
    public function oltList(): View
    {
        $devices = Olt::latest()->paginate(20);

        $stats = [
            'total' => Olt::count(),
            'active' => Olt::where('status', 'active')->count(),
            'total_onus' => 0,
            'online_onus' => 0,
        ];

        return view('panels.admin.network.olt', compact('devices', 'stats'));
    }

    /**
     * Show create OLT form.
     */
    public function oltCreate(): View
    {
        return view('panels.admin.network.olt-create');
    }

    /**
     * Display OLT dashboard.
     */
    public function oltDashboard(): View
    {
        return view('panels.admin.olt.dashboard');
    }

    /**
     * Display OLT monitor view for a specific OLT.
     */
    public function oltMonitor(int $id): View
    {
        $olt = Olt::findOrFail($id);

        return view('panels.admin.olt.monitor', compact('olt'));
    }

    /**
     * Display OLT performance metrics view.
     */
    public function oltPerformance(int $id): View
    {
        $olt = Olt::findOrFail($id);

        return view('panels.admin.olt.performance', compact('olt'));
    }

    /**
     * Display OLT configuration templates.
     */
    public function oltTemplates(): View
    {
        return view('panels.admin.olt.templates');
    }

    /**
     * Display OLT SNMP traps.
     */
    public function oltSnmpTraps(): View
    {
        return view('panels.admin.olt.snmp-traps');
    }

    /**
     * Display OLT firmware updates.
     */
    public function oltFirmware(): View
    {
        return view('panels.admin.olt.firmware');
    }

    /**
     * Display OLT backup management.
     */
    public function oltBackups(): View
    {
        return view('panels.admin.olt.backups');
    }

    /**
     * Display all network devices.
     */
    public function devices(): View
    {
        // Combine all device types for unified view using a UNION query
        $routerQuery = MikrotikRouter::select('id', 'name', 'host', 'status', 'created_at')
            ->addSelect(DB::raw("'router' as device_type"));

        $oltQuery = Olt::select('id', 'name', DB::raw('ip_address as host'), 'status', 'created_at')
            ->addSelect(DB::raw("'olt' as device_type"));

        $ciscoQuery = CiscoDevice::select('id', 'name', DB::raw('ip_address as host'), 'status', 'created_at')
            ->addSelect(DB::raw("'cisco' as device_type"));

        // Execute a single query using UNION ALL and order results in the database
        $devices = $routerQuery
            ->unionAll($oltQuery)
            ->unionAll($ciscoQuery)
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $devices->count(),
            'routers' => MikrotikRouter::count(),
            'olts' => Olt::count(),
            'switches' => CiscoDevice::count(),
            'online' => $devices->where('status', 'active')->count(),
        ];

        return view('panels.admin.network.devices', compact('devices', 'stats'));
    }

    /**
     * Display device monitoring dashboard.
     */
    public function deviceMonitors(): View
    {
        // Get actual device monitoring data using polymorphic relationships
        $deviceMonitors = DeviceMonitor::with('monitorable')
            ->latest()
            ->limit(50)
            ->get();

        // Calculate health statistics based on device monitoring data
        $onlineCount = DeviceMonitor::online()->count();
        $offlineCount = DeviceMonitor::offline()->count();
        $degradedCount = DeviceMonitor::degraded()->count();
        
        // Total devices from all types
        $totalDevices = MikrotikRouter::count() + Olt::count() + CiscoDevice::count();

        $monitors = [
            'healthy' => $onlineCount,
            'warning' => $degradedCount,
            'critical' => 0, // Placeholder for future threshold-based critical detection
            'offline' => $offlineCount,
            'devices' => $deviceMonitors,
            'alerts' => collect(), // Placeholder for future alert system implementation
        ];

        return view('panels.admin.network.device-monitors', compact('monitors'));
    }

    /**
     * Display devices map view.
     */
    public function devicesMap(): View
    {
        $devices = collect(); // Replace with actual query for devices with location data

        $stats = [
            'online' => 0,
            'offline' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        return view('panels.admin.network.devices-map', compact('devices', 'stats'));
    }

    /**
     * Display IPv4 pools management.
     */
    public function ipv4Pools(): View
    {
        $pools = IpPool::with('subnets')->latest()->paginate(20);

        $stats = [
            'total' => IpPool::count(),
            'available' => 0, // Placeholder for calculating available IPs based on pool capacity minus allocations
            'allocated' => IpAllocation::count(),
            'pools' => $pools->total(),
        ];

        return view('panels.admin.network.ipv4-pools', compact('pools', 'stats'));
    }

    /**
     * Display IPv6 pools management.
     */
    public function ipv6Pools(): View
    {
        // Filter IPv6 pools by checking for colon in start_ip (IPv6 format)
        $pools = IpPool::where('start_ip', 'LIKE', '%:%')->latest()->paginate(20);

        $stats = [
            'pools' => $pools->total(),
            'allocated' => IpAllocation::count(),
            'available' => 0, // Placeholder for calculating available IPs based on subnet capacity
        ];

        return view('panels.admin.network.ipv6-pools', compact('pools', 'stats'));
    }

    /**
     * Display PPPoE profiles management.
     */
    public function pppoeProfiles(): View
    {
        $profiles = MikrotikProfile::with('router')->latest()->paginate(20);

        $stats = [
            'total' => MikrotikProfile::count(),
            'active' => MikrotikProfile::count(), // Currently counts all profiles; adjust if a status field is introduced
            'users' => NetworkUser::count(),
        ];

        return view('panels.admin.network.pppoe-profiles', compact('profiles', 'stats'));
    }

    /**
     * Show FUP editor for package.
     */
    public function packageFupEdit($id): View
    {
        $package = ServicePackage::findOrFail($id);

        return view('panels.admin.network.package-fup-edit', compact('package'));
    }

    /**
     * Display ping test tool.
     */
    public function pingTest(): View
    {
        return view('panels.admin.network.ping-test');
    }

    /**
     * Display send SMS form.
     */
    public function smsSend(): View
    {
        return view('panels.admin.sms.send');
    }

    /**
     * Display SMS broadcast form.
     */
    public function smsBroadcast(): View
    {
        return view('panels.admin.sms.broadcast');
    }

    /**
     * Display SMS history.
     */
    public function smsHistories(): View
    {
        return view('panels.admin.sms.histories');
    }

    /**
     * Display SMS events configuration.
     */
    public function smsEvents(): View
    {
        return view('panels.admin.sms.events');
    }

    /**
     * Display due date notification configuration.
     */
    public function dueDateNotification(): View
    {
        return view('panels.admin.sms.due-date-notification');
    }

    /**
     * Display payment link broadcast form.
     */
    public function paymentLinkBroadcast(): View
    {
        return view('panels.admin.sms.payment-link-broadcast');
    }

    /**
     * Display router logs.
     */
    public function routerLogs(): View
    {
        // Get router connection logs from audit logs
        $logs = \App\Models\AuditLog::where('auditable_type', MikrotikRouter::class)
            ->orWhere('event', 'like', '%router%')
            ->with(['user', 'auditable'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => \App\Models\AuditLog::where('auditable_type', MikrotikRouter::class)->count(),
            'today' => \App\Models\AuditLog::where('auditable_type', MikrotikRouter::class)->whereDate('created_at', today())->count(),
            'this_week' => \App\Models\AuditLog::where('auditable_type', MikrotikRouter::class)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => \App\Models\AuditLog::where('auditable_type', MikrotikRouter::class)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('panels.admin.logs.router', compact('logs', 'stats'));
    }

    /**
     * Display RADIUS logs.
     */
    public function radiusLogs(): View
    {
        // Get RADIUS accounting logs
        $logs = \App\Models\RadAcct::with('user')
            ->latest('acctstarttime')
            ->paginate(50);

        $stats = [
            'total' => \App\Models\RadAcct::count(),
            'today' => \App\Models\RadAcct::whereDate('acctstarttime', today())->count(),
            'active_sessions' => \App\Models\RadAcct::whereNull('acctstoptime')->count(),
            'total_bandwidth' => \App\Models\RadAcct::sum('acctinputoctets') + \App\Models\RadAcct::sum('acctoutputoctets'),
        ];

        return view('panels.admin.logs.radius', compact('logs', 'stats'));
    }

    /**
     * Display scheduler logs.
     */
    public function schedulerLogs(): View
    {
        // Read scheduler log file if it exists
        $logFile = storage_path('logs/scheduler.log');
        $logs = collect();
        
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            
            // Get last 100 scheduler entries
            $recentLines = array_slice($lines, -100);
            $parsedLogs = [];
            
            foreach ($recentLines as $line) {
                if (empty(trim($line))) continue;
                
                if (preg_match('/\[(.*?)\]\s+(.*)/', $line, $matches)) {
                    $parsedLogs[] = [
                        'timestamp' => $matches[1] ?? now()->toDateTimeString(),
                        'message' => $matches[2] ?? $line,
                    ];
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

        $stats = [
            'total' => $logs->total(),
            'file_size' => file_exists($logFile) ? filesize($logFile) : 0,
        ];

        return view('panels.admin.logs.scheduler', compact('logs', 'stats'));
    }

    /**
     * Display activity logs.
     */
    public function activityLogs(): View
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

        return view('panels.admin.logs.activity', compact('logs', 'stats'));
    }

    /**
     * Display Laravel application logs.
     */
    public function laravelLogs(): View
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
                if (empty(trim($line))) continue;
                
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

        return view('panels.admin.logs.laravel', compact('logs', 'stats'));
    }

    /**
     * Display PPP connection/disconnection logs.
     */
    public function pppLogs(): View
    {
        $user = auth()->user();
        $userRole = $user->roles->first()?->slug ?? '';
        
        // Base query for PPP sessions from RADIUS accounting
        $query = \App\Models\RadAcct::where('username', 'LIKE', '%ppp%')
            ->orWhere('nasporttype', 'PPP');
        
        // Filter by ownership for non-admin roles
        if (!in_array($userRole, ['developer', 'super-admin', 'admin', 'manager'])) {
            // For operators and staff, show only their assigned customers
            if ($userRole === 'operator' || $userRole === 'staff') {
                $customerIds = $user->customers()->pluck('id')->toArray();
                $query->whereHas('user', function ($q) use ($customerIds) {
                    $q->whereIn('id', $customerIds);
                });
            }
            // For customers, show only their own logs
            elseif ($userRole === 'customer') {
                $query->where('username', $user->username);
            }
        }
        
        $logs = $query->latest('acctstarttime')->paginate(50);

        $stats = [
            'total' => $query->count(),
            'today' => (clone $query)->whereDate('acctstarttime', today())->count(),
            'active_sessions' => (clone $query)->whereNull('acctstoptime')->count(),
            'total_bandwidth' => $query->sum('acctinputoctets') + $query->sum('acctoutputoctets'),
        ];

        return view('panels.admin.logs.ppp', compact('logs', 'stats'));
    }

    /**
     * Display Hotspot connection/disconnection logs.
     */
    public function hotspotLogs(): View
    {
        $user = auth()->user();
        $userRole = $user->roles->first()?->slug ?? '';
        
        // Base query for Hotspot sessions from RADIUS accounting
        $query = \App\Models\RadAcct::where('username', 'NOT LIKE', '%ppp%')
            ->where(function ($q) {
                $q->where('nasporttype', 'Wireless-802.11')
                  ->orWhere('nasporttype', 'Ethernet')
                  ->orWhereNull('nasporttype');
            });
        
        // Filter by ownership for non-admin roles
        if (!in_array($userRole, ['developer', 'super-admin', 'admin', 'manager'])) {
            // For operators and staff, show only their assigned customers
            if ($userRole === 'operator' || $userRole === 'staff') {
                $customerIds = $user->customers()->pluck('id')->toArray();
                $query->whereHas('user', function ($q) use ($customerIds) {
                    $q->whereIn('id', $customerIds);
                });
            }
            // For customers, show only their own logs
            elseif ($userRole === 'customer') {
                $query->where('username', $user->username);
            }
        }
        
        $logs = $query->latest('acctstarttime')->paginate(50);

        $stats = [
            'total' => $query->count(),
            'today' => (clone $query)->whereDate('acctstarttime', today())->count(),
            'active_sessions' => (clone $query)->whereNull('acctstoptime')->count(),
            'total_bandwidth' => $query->sum('acctinputoctets') + $query->sum('acctoutputoctets'),
        ];

        return view('panels.admin.logs.hotspot', compact('logs', 'stats'));
    }
}
