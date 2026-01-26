<?php

namespace App\Http\Controllers\Panel;

use App\Exports\InvoicesExport;
use App\Exports\PaymentsExport;
use App\Http\Controllers\Controller;
use App\Models\CiscoDevice;
use App\Models\DeviceMonitor;
use App\Models\Invoice;
use App\Models\IpAllocation;
use App\Models\IpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Models\NetworkUser;
use App\Models\Olt;
use App\Models\OperatorPackageRate;
use App\Models\OperatorSmsRate;
use App\Models\OperatorWalletTransaction;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\CustomerCacheService;
use App\Services\CustomerFilterService;
use App\Services\ExcelExportService;
use App\Services\MikrotikService;
use App\Services\PdfExportService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // Billing statistics
            'billed_customers' => Invoice::distinct('user_id')->count('user_id'),
            'total_invoices' => Invoice::count(),
            'total_billed_amount' => Invoice::sum('total_amount'),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            // Today's Update statistics
            'new_customers_today' => User::whereDate('created_at', today())
                ->whereHas('roles', function ($query) {
                    $query->where('slug', 'customer');
                })->count(),
            'payments_today' => Payment::whereDate('payment_date', today())
                ->where('status', 'success')
                ->sum('amount'),
            'tickets_today' => \App\Models\Ticket::whereDate('created_at', today())->count(),
            'expiring_today' => NetworkUser::whereDate('expiry_date', today())
                ->whereHas('user.roles', function ($query) {
                    $query->where('slug', 'customer');
                })
                ->count(),
            // Additional customer statistics
            'online_customers' => NetworkUser::has('sessions')->count(),
            'offline_customers' => NetworkUser::doesntHave('sessions')->count(),
            'suspended_customers' => NetworkUser::where('status', 'suspended')->count(),
            'pppoe_customers' => NetworkUser::where('service_type', 'pppoe')->count(),
            'hotspot_customers' => NetworkUser::where('service_type', 'hotspot')->count(),
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
     * Show create user form.
     */
    public function usersCreate(): View
    {
        return view('panels.admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,slug',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_active' => true,
        ]);

        // Assign role using the model method that handles tenant_id
        $user->assignRole($validated['role']);

        return redirect()->route('panel.admin.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show edit user form.
     */
    public function usersEdit($id): View
    {
        $user = User::with('roles')->findOrFail($id);

        return view('panels.admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,slug',
            'is_active' => 'nullable|boolean',
        ]);

        // Update user data
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ];

        // Only update password if provided
        if (! empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        // Update role - detach all and assign new role with proper tenant_id
        $user->roles()->detach();
        $user->assignRole($validated['role']);

        return redirect()->route('panel.admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function usersDestroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('panel.admin.users')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('panel.admin.users')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Display network users listing.
     */
    public function networkUsers(): View
    {
        $networkUsers = NetworkUser::with(['user', 'package'])->latest()->paginate(20);

        $stats = [
            'active' => NetworkUser::where('status', 'active')->count(),
            'suspended' => NetworkUser::where('status', 'suspended')->count(),
            'inactive' => NetworkUser::where('status', 'inactive')->count(),
            'total' => NetworkUser::count(),
        ];

        return view('panels.admin.network-users.index', compact('networkUsers', 'stats'));
    }

    /**
     * Show the form for creating a new network user.
     */
    public function networkUsersCreate(): View
    {
        $customers = User::whereHas('roles', function ($query) {
            $query->where('slug', 'customer');
        })->get();
        $packages = Package::where('status', 'active')->get();
        $routers = MikrotikRouter::where('status', 'active')->get();

        return view('panels.admin.network-users.create', compact('customers', 'packages', 'routers'));
    }

    /**
     * Store a newly created network user.
     */
    public function networkUsersStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'username' => 'required|string|max:255|unique:network_users,username',
            'password' => 'required|string|min:6',
            'package_id' => 'required|exists:packages,id',
            'service_type' => 'required|in:pppoe,hotspot,static',
            'status' => 'required|in:active,suspended,inactive',
        ]);

        // Don't store the password in the database - it should be managed via router API
        $networkUserData = [
            'user_id' => $validated['user_id'],
            'username' => $validated['username'],
            'package_id' => $validated['package_id'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
        ];

        $networkUser = NetworkUser::create($networkUserData);

        // Push the password to the actual router via MikrotikService
        // SECURITY WARNING: MikrotikService currently uses HTTP for router communication.
        // For production environments, configure HTTPS with certificate validation in the
        // MikrotikService to protect credentials during transmission. See MikrotikService
        // class documentation for security considerations.
        if ($validated['service_type'] === 'pppoe') {
            // Select router with explicit ordering for consistency
            $router = MikrotikRouter::where('status', 'active')
                ->orderBy('id')
                ->first();

            if ($router) {
                try {
                    $mikrotikService = app(MikrotikService::class);

                    // Resolve PPPoE profile for this package and router
                    $profileName = 'default';
                    $profileMapping = PackageProfileMapping::where('package_id', $validated['package_id'])
                        ->where('router_id', $router->id)
                        ->first();

                    if ($profileMapping && ! empty($profileMapping->profile_name)) {
                        $profileName = $profileMapping->profile_name;
                    }

                    $mikrotikService->createPppoeUser([
                        'router_id' => $router->id,
                        'username' => $validated['username'],
                        'password' => $validated['password'],
                        'service' => 'pppoe',
                        'profile' => $profileName,
                    ]);
                } catch (\Exception $e) {
                    // Log the error but don't fail the user creation
                    Log::warning('Failed to sync network user to router', [
                        'username' => $validated['username'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('panel.admin.network-users')
            ->with('success', 'Network user created successfully.');
    }

    /**
     * Display the specified network user.
     */
    public function networkUsersShow($id): View
    {
        $networkUser = NetworkUser::with(['user', 'package'])->findOrFail($id);

        return view('panels.admin.network-users.show', compact('networkUser'));
    }

    /**
     * Show the form for editing the specified network user.
     */
    public function networkUsersEdit($id): View
    {
        $networkUser = NetworkUser::findOrFail($id);
        $customers = User::whereHas('roles', function ($query) {
            $query->where('slug', 'customer');
        })->get();
        $packages = Package::where('status', 'active')->get();
        $routers = MikrotikRouter::where('status', 'active')->get();

        return view('panels.admin.network-users.edit', compact('networkUser', 'customers', 'packages', 'routers'));
    }

    /**
     * Update the specified network user.
     */
    public function networkUsersUpdate(Request $request, $id)
    {
        $networkUser = NetworkUser::findOrFail($id);

        // Capture original username before update for router sync
        $originalUsername = $networkUser->username;

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'username' => 'required|string|max:255|unique:network_users,username,' . $id,
            'password' => 'nullable|string|min:6',
            'package_id' => 'required|exists:packages,id',
            'service_type' => 'required|in:pppoe,hotspot,static',
            'status' => 'required|in:active,suspended,inactive',
        ]);

        // Update only the allowed fields (not password)
        $networkUserData = [
            'user_id' => $validated['user_id'],
            'username' => $validated['username'],
            'package_id' => $validated['package_id'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
        ];

        $networkUser->update($networkUserData);

        // If password is provided, update it on the router via MikrotikService
        // SECURITY WARNING: MikrotikService currently uses HTTP for router communication.
        // For production environments, configure HTTPS with certificate validation in the
        // MikrotikService to protect credentials during transmission. See MikrotikService
        // class documentation for security considerations.
        if (! empty($validated['password']) && $validated['service_type'] === 'pppoe') {
            try {
                $mikrotikService = app(MikrotikService::class);

                // Use original username to locate the user on the router
                $mikrotikService->updatePppoeUser($originalUsername, [
                    'password' => $validated['password'],
                ]);
            } catch (\Exception $e) {
                // Log the error but don't fail the update
                Log::warning('Failed to sync password update to router', [
                    'original_username' => $originalUsername,
                    'new_username' => $validated['username'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('panel.admin.network-users')
            ->with('success', 'Network user updated successfully.');
    }

    /**
     * Remove the specified network user.
     */
    public function networkUsersDestroy($id)
    {
        $networkUser = NetworkUser::findOrFail($id);
        $networkUser->delete();

        return redirect()->route('panel.admin.network-users')
            ->with('success', 'Network user deleted successfully.');
    }

    /**
     * Show the form for importing network users from router.
     */
    public function networkUsersImport(): View
    {
        $routers = MikrotikRouter::where('status', 'active')->get();

        return view('panels.admin.network-users.import', compact('routers'));
    }

    /**
     * Process the import of network users from router.
     */
    public function networkUsersProcessImport(Request $request)
    {
        $validated = $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
            'skip_existing' => 'boolean',
            'auto_create_customers' => 'boolean',
            'sync_packages' => 'boolean',
        ]);

        try {
            $mikrotikService = app(\App\Services\MikrotikService::class);

            // Import secrets (PPPoE users) from router
            $secrets = $mikrotikService->importSecrets($validated['router_id']);

            if (empty($secrets)) {
                return redirect()->route('panel.admin.network-users.import')
                    ->with('error', 'No users found on the selected router or unable to connect.');
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($secrets as $secret) {
                try {
                    // Skip if user already exists
                    if ($validated['skip_existing'] ?? true) {
                        if (NetworkUser::where('username', $secret['name'])->exists()) {
                            $skipped++;

                            continue;
                        }
                    }

                    // Find or create customer if auto_create_customers is enabled
                    $userId = null;
                    if ($validated['auto_create_customers'] ?? false) {
                        $emailDomain = config('app.imported_user_domain', 'imported.local');
                        $customer = User::firstOrCreate(
                            ['email' => $secret['name'] . '@' . $emailDomain],
                            [
                                'name' => $secret['name'],
                                'password' => bcrypt(Str::random(32)), // Strong random password
                            ]
                        );
                        $userId = $customer->id;
                    }

                    // Find package by profile name if sync_packages is enabled
                    $packageId = null;
                    if ($validated['sync_packages'] ?? true) {
                        $package = Package::where('name', 'like', '%' . ($secret['profile'] ?? 'default') . '%')->first();
                        $packageId = $package?->id;
                    }

                    // Normalize disabled flag and determine status
                    $disabledRaw = $secret['disabled'] ?? false;
                    $isDisabled = in_array($disabledRaw, [true, 1, '1', 'yes', 'true', 'on'], true);
                    $status = $isDisabled ? 'inactive' : 'active';

                    // Create network user - don't store the password
                    NetworkUser::create([
                        'user_id' => $userId,
                        'username' => $secret['name'],
                        'service_type' => $secret['service'] ?? 'pppoe',
                        'package_id' => $packageId,
                        'status' => $status,
                    ]);

                    // Note: Passwords remain on the router and are not stored in our database
                    // for security reasons. Users must be managed via the router API.

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to import user {$secret['name']}: " . $e->getMessage();
                }
            }

            $message = "Successfully imported {$imported} network users.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} existing users.";
            }
            if (! empty($errors)) {
                $message .= ' Encountered ' . count($errors) . ' errors.';
            }

            return redirect()->route('panel.admin.network-users')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            Log::error('Network users import failed', [
                'router_id' => $validated['router_id'],
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('panel.admin.network-users.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
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
     * Show the form for creating a new package.
     */
    public function packagesCreate(): View
    {
        return view('panels.admin.packages.create');
    }

    /**
     * Store a newly created package.
     */
    public function packagesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'bandwidth_up' => 'nullable|integer|min:0',
            'bandwidth_down' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'validity_days' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        // Tenant ID is automatically set by BelongsToTenant trait
        ServicePackage::create($validated);

        return redirect()->route('panel.admin.packages')
            ->with('success', 'Package created successfully.');
    }

    /**
     * Show the form for editing the specified package.
     */
    public function packagesEdit($id): View
    {
        // Find package within current tenant scope (automatically filtered by BelongsToTenant trait)
        $package = ServicePackage::findOrFail($id);

        return view('panels.admin.packages.edit', compact('package'));
    }

    /**
     * Update the specified package.
     */
    public function packagesUpdate(Request $request, $id)
    {
        // Find package within current tenant scope (automatically filtered by BelongsToTenant trait)
        $package = ServicePackage::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'bandwidth_up' => 'nullable|integer|min:0',
            'bandwidth_down' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'validity_days' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $package->update($validated);

        return redirect()->route('panel.admin.packages')
            ->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified package.
     */
    public function packagesDestroy($id)
    {
        // Find package within current tenant scope (automatically filtered by BelongsToTenant trait)
        $package = ServicePackage::findOrFail($id);
        $package->delete();

        return redirect()->route('panel.admin.packages')
            ->with('success', 'Package deleted successfully.');
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
     * Display customers listing with advanced filtering and caching.
     */
    public function customers(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $roleId = auth()->user()->role_id;
        $refresh = $request->boolean('refresh', false);
        $perPage = $request->input('per_page', session('customers_per_page', 25));
        
        // Save pagination preference
        if ($request->has('per_page')) {
            session(['customers_per_page' => $perPage]);
        }

        // Initialize services
        $cacheService = app(CustomerCacheService::class);
        $filterService = app(CustomerFilterService::class);

        // Get cached customers
        $allCustomers = $cacheService->getCustomers($tenantId, $roleId, $refresh);

        // Attach online status
        $allCustomers = $cacheService->attachOnlineStatus($allCustomers, $refresh);

        // Apply filters
        $filters = $request->only([
            'connection_type',
            'billing_type',
            'status',
            'payment_status',
            'zone_id',
            'package_id',
            'device_type',
            'expiry_date_from',
            'expiry_date_to',
            'registration_date_from',
            'registration_date_to',
            'last_payment_date_from',
            'last_payment_date_to',
            'balance_min',
            'balance_max',
            'online_status',
            'search',
        ]);

        $filteredCustomers = $filterService->applyFilters($allCustomers, $filters);

        // Manual pagination
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $total = $filteredCustomers->count();
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredCustomers->slice($offset, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get filter options
        $filterOptions = $filterService->getFilterOptions($tenantId);
        $packages = ServicePackage::where('tenant_id', $tenantId)->get();
        $zones = \App\Models\Zone::where('tenant_id', $tenantId)->get();

        $stats = [
            'total' => $allCustomers->count(),
            'active' => $allCustomers->where('status', 'active')->count(),
            'online' => $allCustomers->where('online_status', true)->count(),
            'offline' => $allCustomers->where('online_status', false)->count(),
            'filtered' => $total,
        ];

        return view('panels.admin.customers.index', compact(
            'customers',
            'packages',
            'zones',
            'stats',
            'filters',
            'filterOptions',
            'perPage'
        ));
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
     * Store a newly created customer.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customersStore(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:network_users,username|regex:/^[a-zA-Z0-9_-]+$/',
            'password' => 'required|string|min:8',
            'service_type' => 'required|in:pppoe,hotspot,cable-tv,static-ip,other',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // Hash the password and set is_active to true by default
        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = true;

        // Create the network user
        NetworkUser::create($validated);

        return redirect()->route('panel.admin.customers')
            ->with('success', 'Customer created successfully.');
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
     * Update the specified customer.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customersUpdate(Request $request, $id)
    {
        $customer = NetworkUser::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:network_users,username,' . $id . '|regex:/^[a-zA-Z0-9_-]+$/',
            'password' => 'nullable|string|min:8',
            'service_type' => 'required|in:pppoe,hotspot,cable-tv,static-ip,other',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // Prepare update data
        $updateData = [
            'username' => $validated['username'],
            'service_type' => $validated['service_type'],
            'package_id' => $validated['package_id'],
            'status' => $validated['status'],
        ];

        // Only update password if provided
        if (! empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $customer->update($updateData);

        return redirect()->route('panel.admin.customers')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customersDestroy($id)
    {
        $customer = NetworkUser::findOrFail($id);
        $customer->delete();

        return redirect()->route('panel.admin.customers')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Show customer detail.
     */
    public function customersShow($id): View
    {
        // Eager load relationships to avoid N+1 queries
        $customer = NetworkUser::with(['package', 'sessions', 'user'])->findOrFail($id);
        
        // Load ONU information if exists
        $onu = \App\Models\Onu::where('network_user_id', $id)->with('olt')->first();

        return view('panels.admin.customers.show', compact('customer', 'onu'));
    }

    /**
     * Suspend a customer.
     */
    public function customersSuspend($id)
    {
        try {
            $customer = NetworkUser::with('user')->findOrFail($id);
            
            // Authorization check on the related User model
            if ($customer->user) {
                $this->authorize('suspend', $customer->user);
            }
            
            // Prevent suspending already suspended customers
            if ($customer->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is already suspended.'
                ], 400);
            }
            
            $customer->status = 'suspended';
            $customer->save();
            
            // Clear cache if CustomerCacheService is being used
            if (class_exists('\App\Services\CustomerCacheService')) {
                \Cache::tags(['customers'])->flush();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Customer suspended successfully.'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to suspend this customer.'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('Failed to suspend customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend customer. Please try again.'
            ], 500);
        }
    }

    /**
     * Activate a customer.
     */
    public function customersActivate($id)
    {
        try {
            $customer = NetworkUser::with('user')->findOrFail($id);
            
            // Authorization check on the related User model
            if ($customer->user) {
                $this->authorize('activate', $customer->user);
            }
            
            // Prevent activating already active customers
            if ($customer->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is already active.'
                ], 400);
            }
            
            $customer->status = 'active';
            $customer->save();
            
            // Clear cache if CustomerCacheService is being used
            if (class_exists('\App\Services\CustomerCacheService')) {
                \Cache::tags(['customers'])->flush();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Customer activated successfully.'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to activate this customer.'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('Failed to activate customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate customer. Please try again.'
            ], 500);
        }
    }

    /**
     * Display deleted customers.
     */
    public function deletedCustomers(): View
    {
        // Soft delete functionality not yet implemented for customers
        // This feature requires adding SoftDeletes trait to User model
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
        // Customer import request tracking not yet implemented
        // This feature requires creating an ImportRequest model and migration
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
    public function customerPayments(Request $request): View
    {
        $query = \App\Models\Payment::with(['user', 'invoice'])
            ->latest();

        // Search by customer name, username, or invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                    $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                });
            });
        }

        // Filter by payment method
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $payments = $query->paginate(50);

        // Calculate statistics
        $stats = [
            'total_collected' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
            'this_month' => \App\Models\Payment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'total_payments' => \App\Models\Payment::count(),
            'pending_amount' => \App\Models\Payment::where('status', 'pending')->sum('amount'),
        ];

        return view('panels.admin.accounting.customer-payments', compact('payments', 'stats'));
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
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // Build base query with tenant scoping (unless Developer)
        $baseQuery = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn('slug', ['manager', 'staff', 'operator', 'sub-operator']);
            });

        // Apply tenant filtering for non-Developer users
        if (! $user->isDeveloper() && $tenantId) {
            $baseQuery->where('tenant_id', $tenantId);
        }

        $operators = $baseQuery->latest()->paginate(20);

        // Stats queries with same tenant scoping
        $statsQuery = function () use ($user, $tenantId) {
            $query = User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['manager', 'staff', 'operator', 'sub-operator']);
            });
            if (! $user->isDeveloper() && $tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            return $query;
        };

        $stats = [
            'total' => $statsQuery()->count(),
            'active' => $statsQuery()->where('is_active', true)->count(),
            'managers' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'manager');
            })->when(! $user->isDeveloper() && $tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->count(),
            'staff' => User::whereHas('roles', function ($query) {
                $query->where('slug', 'staff');
            })->when(! $user->isDeveloper() && $tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
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
     * Store a newly created operator.
     */
    public function operatorsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'payment_type' => 'required|in:prepaid,postpaid',
            'credit_limit' => 'nullable|numeric|min:0',
            'sms_charges_by' => 'required|in:admin,operator',
            'sms_cost_per_unit' => 'nullable|numeric|min:0',
            'allow_sub_operator' => 'nullable|boolean',
            'allow_rename_package' => 'nullable|boolean',
            'can_manage_customers' => 'nullable|boolean',
            'can_view_financials' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Create the user with all fields
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_phone' => $validated['company_phone'] ?? null,
            'payment_type' => $validated['payment_type'],
            'credit_limit' => $validated['payment_type'] === 'postpaid' ? ($validated['credit_limit'] ?? 0) : 0,
            'sms_charges_by' => $validated['sms_charges_by'],
            'sms_cost_per_unit' => $validated['sms_charges_by'] === 'operator' ? ($validated['sms_cost_per_unit'] ?? 0) : 0,
            'allow_sub_operator' => $request->has('allow_sub_operator'),
            'allow_rename_package' => $request->has('allow_rename_package'),
            'can_manage_customers' => array_key_exists('can_manage_customers', $validated) ? (bool) $validated['can_manage_customers'] : true,
            'can_view_financials' => array_key_exists('can_view_financials', $validated) ? (bool) $validated['can_view_financials'] : true,
            'is_active' => $request->has('is_active'),
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'operator_type' => 'operator',
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        // Assign operator role using the model method
        $user->assignRole('operator');

        return redirect()->route('panel.admin.operators')
            ->with('success', 'Operator created successfully with all configurations.');
    }

    /**
     * Update the specified operator.
     */
    public function operatorsUpdate(Request $request, $id)
    {
        $operator = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'operator_type' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Update user data
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'operator_type' => $validated['operator_type'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ];

        // Only update password if provided
        if (! empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $operator->update($updateData);

        return redirect()->route('panel.admin.operators')
            ->with('success', 'Operator updated successfully.');
    }

    /**
     * Remove the specified operator.
     */
    public function operatorsDestroy($id)
    {
        $operator = User::findOrFail($id);

        // Prevent deleting own account
        if ($operator->id === auth()->id()) {
            return redirect()->route('panel.admin.operators')
                ->with('error', 'You cannot delete your own account.');
        }

        $operator->delete();

        return redirect()->route('panel.admin.operators')
            ->with('success', 'Operator deleted successfully.');
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
     * Update operator special permissions.
     */
    public function updateOperatorSpecialPermissions(Request $request, $id)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $operator = User::findOrFail($id);

        // Here you would update the operator's permissions
        // This depends on your permission system implementation
        // Example: $operator->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('panel.admin.operators.special-permissions', $id)
            ->with('success', 'Special permissions updated successfully.');
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
     * Store payment gateway.
     */
    public function paymentGatewaysStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:bkash,nagad,rocket,ssl_commerz,aamarpay,stripe,paypal',
            'environment' => 'required|string|in:sandbox,production',
            'status' => 'required|string|in:active,inactive,testing,maintenance',
            'merchant_id' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:255',
        ]);

        // Normalize type value to match PaymentGateway slug constants
        $slug = $validated['type'];
        if ($slug === 'ssl_commerz') {
            $slug = PaymentGateway::TYPE_SSLCOMMERZ;
        }

        // Build configuration array
        $configuration = [
            'merchant_id' => $validated['merchant_id'],
            'api_key' => $validated['api_key'],
            'api_secret' => $validated['api_secret'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'environment' => $validated['environment'],
        ];

        PaymentGateway::create([
            'tenant_id' => getCurrentTenantId(),
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['status'] === 'active',
            'test_mode' => $validated['environment'] === 'sandbox',
            'configuration' => $configuration,
        ]);

        return redirect()->route('panel.admin.payment-gateways')
            ->with('success', 'Payment gateway configured successfully.');
    }

    /**
     * Display network routers listing.
     */
    public function routers(): View
    {
        $routers = MikrotikRouter::with('pppoeUsers')->paginate(20);

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
     * Store a newly created router.
     */
    public function routersStore(Request $request)
    {
        $validated = $request->validate([
            'router_name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:mikrotik_routers,ip_address',
            'username' => 'required|string|max:100',
            'password' => 'required|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        // Map form fields to database columns
        MikrotikRouter::create([
            'name' => $validated['router_name'],
            'ip_address' => $validated['ip_address'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'api_port' => $validated['port'] ?? 8728,
            'status' => $validated['status'] === 'maintenance' ? 'inactive' : $validated['status'],
        ]);

        return redirect()->route('panel.admin.network.routers')
            ->with('success', 'Router created successfully.');
    }

    /**
     * Show edit router form.
     */
    public function routersEdit($id): View
    {
        $router = MikrotikRouter::findOrFail($id);

        return view('panels.admin.network.routers-edit', compact('router'));
    }

    /**
     * Update the specified router.
     */
    public function routersUpdate(Request $request, $id)
    {
        $router = MikrotikRouter::findOrFail($id);

        $validated = $request->validate([
            'router_name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:mikrotik_routers,ip_address,' . $id,
            'username' => 'required|string|max:100',
            'password' => 'nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        // Map form fields to database columns
        $updateData = [
            'name' => $validated['router_name'],
            'ip_address' => $validated['ip_address'],
            'username' => $validated['username'],
            'api_port' => $validated['port'] ?? $router->api_port,
            'status' => $validated['status'] === 'maintenance' ? 'inactive' : $validated['status'],
        ];

        // Only update password if provided
        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $router->update($updateData);

        return redirect()->route('panel.admin.network.routers')
            ->with('success', 'Router updated successfully.');
    }

    /**
     * Remove the specified router.
     */
    public function routersDestroy($id)
    {
        $router = MikrotikRouter::findOrFail($id);
        $router->delete();

        return redirect()->route('panel.admin.network.routers')
            ->with('success', 'Router deleted successfully.');
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
        $routerQuery = MikrotikRouter::select('id', 'name', 'ip_address as host', 'status', 'created_at')
            ->addSelect(DB::raw("'router' as device_type"));

        $oltQuery = Olt::select('id', 'name', DB::raw('ip_address as host'), 'status', 'created_at')
            ->addSelect(DB::raw("'olt' as device_type"));

        $ciscoQuery = CiscoDevice::select('id', 'name', DB::raw('ip_address as host'), 'status', 'created_at')
            ->addSelect(DB::raw("'cisco' as device_type"));

        // Execute paginated query using UNION ALL
        $devices = $routerQuery
            ->unionAll($oltQuery)
            ->unionAll($ciscoQuery)
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => MikrotikRouter::count() + Olt::count() + CiscoDevice::count(),
            'routers' => MikrotikRouter::count(),
            'olts' => Olt::count(),
            'switches' => CiscoDevice::count(),
            'online' => MikrotikRouter::where('status', 'active')->count() +
                        Olt::where('status', 'active')->count() +
                        CiscoDevice::where('status', 'active')->count(),
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
     * Show create IPv4 pool form.
     */
    public function ipv4PoolsCreate(): View
    {
        return view('panels.admin.network.ipv4-pools-create');
    }

    /**
     * Store a newly created IPv4 pool.
     */
    public function ipv4PoolsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_ip' => 'required|ip',
            'end_ip' => 'required|ip',
            'gateway' => 'nullable|ip',
            'dns_primary' => 'nullable|ip',
            'dns_secondary' => 'nullable|ip',
            'description' => 'nullable|string',
        ]);

        // Map DNS fields to the schema's dns_servers column
        $dnsServers = array_filter([
            $validated['dns_primary'] ?? null,
            $validated['dns_secondary'] ?? null,
        ]);

        IpPool::create([
            'name' => $validated['name'],
            'start_ip' => $validated['start_ip'],
            'end_ip' => $validated['end_ip'],
            'gateway' => $validated['gateway'] ?? null,
            'dns_servers' => ! empty($dnsServers) ? implode(',', $dnsServers) : null,
            'description' => $validated['description'] ?? null,
            'pool_type' => 'ipv4',
            'status' => 'active',
        ]);

        return redirect()->route('panel.admin.network.ipv4-pools')
            ->with('success', 'IPv4 pool created successfully.');
    }

    /**
     * Show edit IPv4 pool form.
     */
    public function ipv4PoolsEdit($id): View
    {
        $pool = IpPool::findOrFail($id);

        return view('panels.admin.network.ipv4-pools-edit', compact('pool'));
    }

    /**
     * Update the specified IPv4 pool.
     */
    public function ipv4PoolsUpdate(Request $request, $id)
    {
        $pool = IpPool::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_ip' => 'required|ip',
            'end_ip' => 'required|ip',
            'gateway' => 'nullable|ip',
            'dns_primary' => 'nullable|ip',
            'dns_secondary' => 'nullable|ip',
            'description' => 'nullable|string',
        ]);

        // Map DNS fields to the schema's dns_servers column
        $dnsServers = array_filter([
            $validated['dns_primary'] ?? null,
            $validated['dns_secondary'] ?? null,
        ]);

        $pool->update([
            'name' => $validated['name'],
            'start_ip' => $validated['start_ip'],
            'end_ip' => $validated['end_ip'],
            'gateway' => $validated['gateway'] ?? null,
            'dns_servers' => ! empty($dnsServers) ? implode(',', $dnsServers) : null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('panel.admin.network.ipv4-pools')
            ->with('success', 'IPv4 pool updated successfully.');
    }

    /**
     * Remove the specified IPv4 pool.
     */
    public function ipv4PoolsDestroy($id)
    {
        $pool = IpPool::findOrFail($id);
        $pool->delete();

        return redirect()->route('panel.admin.network.ipv4-pools')
            ->with('success', 'IPv4 pool deleted successfully.');
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
     * Show create IPv6 pool form.
     */
    public function ipv6PoolsCreate(): View
    {
        return view('panels.admin.network.ipv6-pools-create');
    }

    /**
     * Store a newly created IPv6 pool.
     */
    public function ipv6PoolsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_ip' => 'required|ipv6',
            'end_ip' => 'required|ipv6',
            'gateway' => 'nullable|ipv6',
            'dns_primary' => 'nullable|ipv6',
            'dns_secondary' => 'nullable|ipv6',
            'description' => 'nullable|string',
        ]);

        // Map DNS fields to the schema's dns_servers column
        $dnsServers = array_filter([
            $validated['dns_primary'] ?? null,
            $validated['dns_secondary'] ?? null,
        ]);

        IpPool::create([
            'name' => $validated['name'],
            'start_ip' => $validated['start_ip'],
            'end_ip' => $validated['end_ip'],
            'gateway' => $validated['gateway'] ?? null,
            'dns_servers' => ! empty($dnsServers) ? implode(',', $dnsServers) : null,
            'description' => $validated['description'] ?? null,
            'pool_type' => 'ipv6',
            'status' => 'active',
        ]);

        return redirect()->route('panel.admin.network.ipv6-pools')
            ->with('success', 'IPv6 pool created successfully.');
    }

    /**
     * Show edit IPv6 pool form.
     */
    public function ipv6PoolsEdit($id): View
    {
        $pool = IpPool::findOrFail($id);

        return view('panels.admin.network.ipv6-pools-edit', compact('pool'));
    }

    /**
     * Update the specified IPv6 pool.
     */
    public function ipv6PoolsUpdate(Request $request, $id)
    {
        $pool = IpPool::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_ip' => 'required|ipv6',
            'end_ip' => 'required|ipv6',
            'gateway' => 'nullable|ipv6',
            'dns_primary' => 'nullable|ipv6',
            'dns_secondary' => 'nullable|ipv6',
            'description' => 'nullable|string',
        ]);

        // Map DNS fields to the schema's dns_servers column
        $dnsServers = array_filter([
            $validated['dns_primary'] ?? null,
            $validated['dns_secondary'] ?? null,
        ]);

        $pool->update([
            'name' => $validated['name'],
            'start_ip' => $validated['start_ip'],
            'end_ip' => $validated['end_ip'],
            'gateway' => $validated['gateway'] ?? null,
            'dns_servers' => ! empty($dnsServers) ? implode(',', $dnsServers) : null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('panel.admin.network.ipv6-pools')
            ->with('success', 'IPv6 pool updated successfully.');
    }

    /**
     * Remove the specified IPv6 pool.
     */
    public function ipv6PoolsDestroy($id)
    {
        $pool = IpPool::findOrFail($id);
        $pool->delete();

        return redirect()->route('panel.admin.network.ipv6-pools')
            ->with('success', 'IPv6 pool deleted successfully.');
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

        $routers = MikrotikRouter::where('status', 'active')->get();

        return view('panels.admin.network.pppoe-profiles', compact('profiles', 'stats', 'routers'));
    }

    /**
     * Store a new PPPoE profile.
     */
    public function pppoeProfilesStore(Request $request)
    {
        $validated = $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure name is unique for the selected router
                \Illuminate\Validation\Rule::unique('mikrotik_profiles')->where(function ($query) use ($request) {
                    return $query->where('router_id', $request->router_id);
                }),
            ],
            'local_address' => 'required|ip',
            'remote_address' => 'required|string|max:255',
            'rate_limit' => 'nullable|string|max:255',
            'session_timeout' => 'nullable|integer|min:0',
            'idle_timeout' => 'nullable|integer|min:0',
        ]);

        MikrotikProfile::create($validated);

        return redirect()->route('panel.admin.network.pppoe-profiles')
            ->with('success', 'PPPoE profile created successfully.');
    }

    /**
     * Delete a PPPoE profile.
     */
    public function pppoeProfilesDestroy($id)
    {
        // Ensure the profile belongs to a router in the current tenant
        $profile = MikrotikProfile::where('id', $id)
            ->whereHas('router')
            ->firstOrFail();

        $profile->delete();

        return redirect()->route('panel.admin.network.pppoe-profiles')
            ->with('success', 'PPPoE profile deleted successfully.');
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
        try {
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle missing RADIUS tables gracefully
            session()->flash('error', 'RADIUS database table not found. Please run: php artisan radius:install --check for details.');

            // Return empty data
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
            $stats = [
                'total' => 0,
                'today' => 0,
                'active_sessions' => 0,
                'total_bandwidth' => 0,
            ];
        }

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
                if (empty(trim($line))) {
                    continue;
                }

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
                if (empty(trim($line))) {
                    continue;
                }

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

        try {
            // Base query for PPP sessions from RADIUS accounting
            $query = \App\Models\RadAcct::where('username', 'LIKE', '%ppp%')
                ->orWhere('nasporttype', 'PPP');

            // Filter by ownership for non-admin roles
            if (! in_array($userRole, ['developer', 'super-admin', 'admin', 'manager'])) {
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle case where radacct table doesn't exist
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                50,
                1,
                ['path' => request()->url()]
            );
            $stats = [
                'total' => 0,
                'today' => 0,
                'active_sessions' => 0,
                'total_bandwidth' => 0,
            ];
            
            // Flash an informational message
            session()->flash('error', 'RADIUS database table not found. Please ensure RADIUS is properly configured and migrations have been run.');
        }

        return view('panels.admin.logs.ppp', compact('logs', 'stats'));
    }

    /**
     * Display Hotspot connection/disconnection logs.
     */
    public function hotspotLogs(): View
    {
        $user = auth()->user();
        $userRole = $user->roles->first()?->slug ?? '';

        try {
            // Base query for Hotspot sessions from RADIUS accounting
            $query = \App\Models\RadAcct::where('username', 'NOT LIKE', '%ppp%')
                ->where(function ($q) {
                    $q->where('nasporttype', 'Wireless-802.11')
                        ->orWhere('nasporttype', 'Ethernet')
                        ->orWhereNull('nasporttype');
                });

            // Filter by ownership for non-admin roles
            if (! in_array($userRole, ['developer', 'super-admin', 'admin', 'manager'])) {
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle case where radacct table doesn't exist
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                50,
                1,
                ['path' => request()->url()]
            );
            $stats = [
                'total' => 0,
                'today' => 0,
                'active_sessions' => 0,
                'total_bandwidth' => 0,
            ];
            
            // Flash an informational message
            session()->flash('error', 'RADIUS database table not found. Please ensure RADIUS is properly configured and migrations have been run.');
        }

        return view('panels.admin.logs.hotspot', compact('logs', 'stats'));
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoicePdf(Invoice $invoice, PdfService $pdfService): StreamedResponse
    {
        // Authorization check - ensure invoice belongs to user's tenant
        $user = auth()->user();
        if ($invoice->tenant_id !== $user->tenant_id && ! $user->isDeveloper()) {
            abort(403, 'Unauthorized access to invoice');
        }

        return $pdfService->downloadInvoicePdf($invoice);
    }

    /**
     * Stream invoice as PDF (display in browser).
     */
    public function streamInvoicePdf(Invoice $invoice, PdfService $pdfService): StreamedResponse
    {
        // Authorization check
        $user = auth()->user();
        if ($invoice->tenant_id !== $user->tenant_id && ! $user->isDeveloper()) {
            abort(403, 'Unauthorized access to invoice');
        }

        return $pdfService->streamInvoicePdf($invoice);
    }

    /**
     * Download payment receipt as PDF.
     */
    public function downloadPaymentReceiptPdf(Payment $payment, PdfService $pdfService): StreamedResponse
    {
        // Authorization check
        $user = auth()->user();
        if ($payment->tenant_id !== $user->tenant_id && ! $user->isDeveloper()) {
            abort(403, 'Unauthorized access to payment');
        }

        return $pdfService->downloadPaymentReceiptPdf($payment);
    }

    /**
     * Export invoices to Excel.
     */
    public function exportInvoices(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = auth()->user();

        // Get invoices based on user's access level
        $query = Invoice::with(['networkUser', 'networkUser.package']);

        // Apply tenant filtering
        if (! $user->isDeveloper()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $invoices = $query->get();

        return Excel::download(new InvoicesExport($invoices), 'invoices-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export payments to Excel.
     */
    public function exportPayments(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = auth()->user();

        // Get payments based on user's access level
        $query = Payment::with(['invoice', 'invoice.networkUser']);

        // Apply tenant filtering
        if (! $user->isDeveloper()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $payments = $query->get();

        return Excel::download(new PaymentsExport($payments), 'payments-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Generate customer statement PDF.
     */
    public function customerStatementPdf(User $customer, PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Authorization check
        if ($customer->tenant_id !== $user->tenant_id && ! $user->isDeveloper()) {
            abort(403, 'Unauthorized access to customer data');
        }

        // Get date range from request or default to current month
        $startDate = request()->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = request()->get('end_date', now()->endOfMonth()->toDateString());

        $pdf = $pdfService->generateCustomerStatementPdf(
            $customer->id,
            $startDate,
            $endDate,
            $user->tenant_id
        );

        return $pdf->download("statement-{$customer->username}-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate monthly report PDF.
     */
    public function monthlyReportPdf(PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Get year and month from request or default to current
        $year = request()->get('year', now()->year);
        $month = request()->get('month', now()->month);

        $pdf = $pdfService->generateMonthlyReportPdf(
            $user->tenant_id,
            $year,
            $month
        );

        return $pdf->download("monthly-report-{$year}-{$month}.pdf");
    }

    /**
     * Export transactions report
     */
    public function exportTransactions(Request $request, ExcelExportService $excelService, PdfExportService $pdfService)
    {
        // $this->authorize('reports.export');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $format = $request->input('format', 'excel');

        // Get transactions data (mock data for now - replace with actual query)
        $transactions = collect([
            (object) [
                'date' => now()->format('Y-m-d'),
                'type' => 'income',
                'description' => 'Payment received',
                'reference' => 'INV-001',
                'amount' => 1000,
                'balance' => 1000,
                'status' => 'completed',
            ],
        ]);

        if ($format === 'pdf') {
            $pdf = $pdfService->generateTransactionsReportPdf($transactions, $startDate, $endDate);

            return $pdf->download('transactions_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return $excelService->exportTransactions($transactions, 'transactions_report');
    }

    /**
     * Export VAT collections report
     */
    public function exportVatCollections(Request $request, ExcelExportService $excelService, PdfExportService $pdfService)
    {
        // $this->authorize('reports.export');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $format = $request->input('format', 'excel');

        // Get VAT collections data from invoices
        $vatCollections = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with('networkUser')
            ->get()
            ->map(function ($invoice) {
                return (object) [
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->networkUser->name ?? 'N/A',
                    'date' => $invoice->created_at->format('Y-m-d'),
                    'subtotal' => $invoice->subtotal ?? $invoice->total_amount / 1.15,
                    'vat_rate' => 15,
                    'vat_amount' => $invoice->vat_amount ?? ($invoice->total_amount * 0.15 / 1.15),
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                ];
            });

        if ($format === 'pdf') {
            $pdf = $pdfService->generateVatCollectionsReportPdf($vatCollections, $startDate, $endDate);

            return $pdf->download('vat_collections_' . now()->format('Y-m-d') . '.pdf');
        }

        return $excelService->exportVatCollections($vatCollections, 'vat_collections');
    }

    /**
     * Export expense report
     */
    public function exportExpenseReport(Request $request, ExcelExportService $excelService, PdfExportService $pdfService)
    {
        // $this->authorize('reports.export');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $format = $request->input('format', 'excel');

        // Get expenses data (mock data for now - replace with actual query)
        $expenses = collect([
            (object) [
                'date' => now()->format('Y-m-d'),
                'category' => 'Operational',
                'description' => 'Office supplies',
                'vendor' => 'ABC Suppliers',
                'amount' => 500,
                'payment_method' => 'cash',
                'status' => 'paid',
                'notes' => 'Monthly supplies',
            ],
        ]);

        if ($format === 'pdf') {
            $pdf = $pdfService->generateExpenseReportPdf($expenses, $startDate, $endDate);

            return $pdf->download('expense_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return $excelService->exportExpenseReport($expenses, 'expense_report');
    }

    /**
     * Export income & expense report
     */
    public function exportIncomeExpenseReport(Request $request, ExcelExportService $excelService, PdfExportService $pdfService)
    {
        // $this->authorize('reports.export');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $format = $request->input('format', 'excel');

        // Get income and expense data
        $incomeData = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get()
            ->map(function ($payment) {
                return (object) [
                    'date' => $payment->paid_at?->format('Y-m-d') ?? $payment->created_at->format('Y-m-d'),
                    'type' => 'income',
                    'category' => 'Payment',
                    'description' => 'Payment from ' . ($payment->user->name ?? 'Customer'),
                    'amount' => $payment->amount,
                    'running_balance' => 0, // Calculate running balance
                ];
            });

        // Mock expense data - replace with actual expense model query
        $expenseData = collect([]);

        $data = $incomeData->merge($expenseData)->sortBy('date');

        if ($format === 'pdf') {
            $pdf = $pdfService->generateIncomeExpenseReportPdf($data, $startDate, $endDate);

            return $pdf->download('income_expense_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return $excelService->exportIncomeExpenseReport($data, $startDate, $endDate, 'income_expense_report');
    }

    /**
     * Export accounts receivable report
     */
    public function exportReceivable(Request $request, ExcelExportService $excelService)
    {
        // $this->authorize('reports.export');

        $format = $request->input('format', 'excel');

        // Get receivables data from unpaid invoices
        $receivables = Invoice::where('status', '!=', 'paid')
            ->with('networkUser')
            ->get()
            ->map(function ($invoice) {
                $dueDate = $invoice->due_date ?? $invoice->created_at->addDays(30);
                $daysOverdue = now()->diffInDays($dueDate, false);

                return (object) [
                    'customer_name' => $invoice->networkUser->name ?? 'N/A',
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->created_at->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount ?? 0,
                    'balance_due' => $invoice->total_amount - ($invoice->paid_amount ?? 0),
                    'days_overdue' => $daysOverdue < 0 ? abs($daysOverdue) : 0,
                    'status' => $invoice->status,
                ];
            });

        return $excelService->exportReceivable($receivables, 'accounts_receivable');
    }

    /**
     * Export accounts payable report
     */
    public function exportPayable(Request $request, ExcelExportService $excelService)
    {
        // $this->authorize('reports.export');

        $format = $request->input('format', 'excel');

        // Mock payables data - replace with actual query when payable model exists
        $payables = collect([
            (object) [
                'vendor_name' => 'Internet Provider',
                'bill_number' => 'BILL-001',
                'bill_date' => now()->subDays(15)->format('Y-m-d'),
                'due_date' => now()->addDays(15)->format('Y-m-d'),
                'total_amount' => 50000,
                'paid_amount' => 0,
                'balance_due' => 50000,
                'days_overdue' => 0,
                'status' => 'pending',
            ],
        ]);

        return $excelService->exportPayable($payables, 'accounts_payable');
    }

    /**
     * Login as operator (impersonate operator).
     */
    public function loginAsOperator(Request $request, int $operatorId)
    {
        $currentUser = auth()->user();

        // Only allow super-admins and admins to impersonate
        if (! $currentUser->hasRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized to impersonate users.');
        }

        // Scope query to current tenant and ensure target is an operator
        $operator = User::where('id', $operatorId)
            ->where('tenant_id', $currentUser->tenant_id)
            ->whereHas('roles', function ($query) {
                $query->whereIn('slug', ['operator', 'sub-operator', 'manager', 'staff']);
            })
            ->firstOrFail();

        // Store original admin ID in session
        session(['impersonate_by' => $currentUser->id]);
        session(['impersonate_at' => now()]);

        // Log audit if AuditLog model exists
        try {
            \App\Models\AuditLog::create([
                'user_id' => $currentUser->id,
                'tenant_id' => $currentUser->tenant_id,
                'event' => 'login_as_operator',
                'auditable_type' => User::class,
                'auditable_id' => $operatorId,
                'new_values' => [
                    'operator_id' => $operatorId,
                    'operator_name' => $operator->name,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Audit logging failed, but continue with impersonation
            \Illuminate\Support\Facades\Log::warning('Failed to log impersonation audit: ' . $e->getMessage());
        }

        // Login as operator
        auth()->loginUsingId($operatorId);

        // Determine redirect route based on impersonated user's role
        $redirectRoute = null;

        if (method_exists($operator, 'hasRole')) {
            // Keep admins / super-admins on the admin dashboard
            if ($operator->hasRole(['super-admin', 'admin'])) {
                $redirectRoute = 'panel.admin.dashboard';
                // Send operators to their own dashboard if route exists
            } elseif ($operator->hasRole('operator')) {
                $redirectRoute = \Illuminate\Support\Facades\Route::has('panel.operator.dashboard')
                    ? 'panel.operator.dashboard'
                    : 'panel.admin.dashboard';
            }
        }

        try {
            if ($redirectRoute !== null) {
                return redirect()->route($redirectRoute)
                    ->with('success', 'You are now logged in as ' . $operator->name)
                    ->with('impersonating', true);
            }

            // Fallback to a generic panel path if no role-specific route is available
            return redirect('/panel')
                ->with('success', 'You are now logged in as ' . $operator->name)
                ->with('impersonating', true);
        } catch (\Exception $e) {
            // If the named route or redirect fails, use the generic panel path
            return redirect('/panel')
                ->with('success', 'You are now logged in as ' . $operator->name)
                ->with('impersonating', true);
        }
    }

    /**
     * Stop impersonating and return to admin account.
     */
    public function stopImpersonating()
    {
        $adminId = session('impersonate_by');

        if (! $adminId) {
            return redirect()->route('panel.admin.dashboard')
                ->with('error', 'No active impersonation session.');
        }

        // Sanity check: ensure the original admin still exists and is allowed
        $admin = User::find($adminId);
        $currentUser = auth()->user();

        if (
            ! $admin ||
            ! $admin->hasRole(['super-admin', 'admin']) ||
            ($currentUser && property_exists($currentUser, 'tenant_id') && $admin->tenant_id !== $currentUser->tenant_id)
        ) {
            // Clear impersonation data and do not restore an invalid or unauthorized admin account
            session()->forget(['impersonate_by', 'impersonate_at']);

            return redirect()->route('panel.admin.dashboard')
                ->with('error', 'Unable to restore the original admin account.');
        }

        // Clear impersonation session data before switching back
        session()->forget(['impersonate_by', 'impersonate_at']);

        auth()->loginUsingId($admin->id);

        return redirect()->route('panel.admin.dashboard')
            ->with('success', 'You are now logged back in as admin.');
    }

    /**
     * Display operator wallet management page.
     */
    public function operatorWallets(): View
    {
        $operators = User::whereHas('roles', function ($query) {
            $query->where('slug', 'operator');
        })->latest()->paginate(20);

        return view('panels.admin.operators.wallets', compact('operators'));
    }

    /**
     * Show form to add funds to operator wallet.
     */
    public function addOperatorFunds(User $operator): View
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        return view('panels.admin.operators.add-funds', compact('operator'));
    }

    /**
     * Process adding funds to operator wallet.
     */
    public function storeOperatorFunds(Request $request, User $operator)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $balanceBefore = $operator->wallet_balance ?? 0;
            $balanceAfter = $balanceBefore + $validated['amount'];

            // Update operator wallet balance
            $operator->update(['wallet_balance' => $balanceAfter]);

            // Record transaction
            OperatorWalletTransaction::create([
                'operator_id' => $operator->id,
                'transaction_type' => 'credit',
                'amount' => $validated['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $validated['description'] ?? 'Manual fund addition by admin',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('panel.admin.operators.wallets')
                ->with('success', 'Funds added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to add funds: ' . $e->getMessage());
        }
    }

    /**
     * Show form to deduct funds from operator wallet.
     */
    public function deductOperatorFunds(User $operator): View
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        return view('panels.admin.operators.deduct-funds', compact('operator'));
    }

    /**
     * Process deducting funds from operator wallet.
     */
    public function processDeductOperatorFunds(Request $request, User $operator)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($operator->wallet_balance ?? 0),
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $balanceBefore = $operator->wallet_balance ?? 0;
            $balanceAfter = $balanceBefore - $validated['amount'];

            // Update operator wallet balance
            $operator->update(['wallet_balance' => $balanceAfter]);

            // Record transaction
            OperatorWalletTransaction::create([
                'operator_id' => $operator->id,
                'transaction_type' => 'debit',
                'amount' => $validated['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $validated['description'] ?? 'Manual fund deduction by admin',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('panel.admin.operators.wallets')
                ->with('success', 'Funds deducted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to deduct funds: ' . $e->getMessage());
        }
    }

    /**
     * Display operator wallet transaction history.
     */
    public function operatorWalletHistory(User $operator): View
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $transactions = OperatorWalletTransaction::where('operator_id', $operator->id)
            ->with('creator')
            ->latest()
            ->paginate(50);

        return view('panels.admin.operators.wallet-history', compact('operator', 'transactions'));
    }

    /**
     * Display operator package rates.
     */
    public function operatorPackageRates(): View
    {
        $operators = User::whereHas('roles', function ($query) {
            $query->where('slug', 'operator');
        })->with('packageRates.package')->latest()->paginate(20);

        return view('panels.admin.operators.package-rates', compact('operators'));
    }

    /**
     * Show form to assign package rates to operator.
     */
    public function assignOperatorPackageRate(User $operator): View
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $packages = Package::where(function ($query) use ($operator) {
            $query->where('is_global', true)
                ->orWhere('operator_id', $operator->id);
        })->get();
        $existingRates = OperatorPackageRate::where('operator_id', $operator->id)
            ->pluck('package_id')
            ->toArray();

        return view('panels.admin.operators.assign-package-rate', compact('operator', 'packages', 'existingRates'));
    }

    /**
     * Store operator package rate assignment.
     */
    public function storeOperatorPackageRate(Request $request, User $operator)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'custom_price' => 'required|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['operator_id'] = $operator->id;

        OperatorPackageRate::updateOrCreate(
            [
                'operator_id' => $operator->id,
                'package_id' => $validated['package_id'],
            ],
            $validated
        );

        return redirect()->route('panel.admin.operators.package-rates')
            ->with('success', 'Package rate assigned successfully.');
    }

    /**
     * Delete operator package rate.
     */
    public function deleteOperatorPackageRate(User $operator, $package)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        OperatorPackageRate::where('operator_id', $operator->id)
            ->where('package_id', $package)
            ->delete();

        return redirect()->route('panel.admin.operators.package-rates')
            ->with('success', 'Package rate removed successfully.');
    }

    /**
     * Display operator SMS rates.
     */
    public function operatorSmsRates(): View
    {
        $operators = User::whereHas('roles', function ($query) {
            $query->where('slug', 'operator');
        })->with('smsRate')->latest()->paginate(20);

        return view('panels.admin.operators.sms-rates', compact('operators'));
    }

    /**
     * Show form to assign SMS rate to operator.
     */
    public function assignOperatorSmsRate(User $operator): View
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $smsRate = OperatorSmsRate::where('operator_id', $operator->id)->first();

        return view('panels.admin.operators.assign-sms-rate', compact('operator', 'smsRate'));
    }

    /**
     * Store operator SMS rate assignment.
     */
    public function storeOperatorSmsRate(Request $request, User $operator)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        $validated = $request->validate([
            'rate_per_sms' => 'required|numeric|min:0',
            'bulk_rate_threshold' => 'required_with:bulk_rate_per_sms|nullable|integer|min:1',
            'bulk_rate_per_sms' => 'required_with:bulk_rate_threshold|nullable|numeric|min:0',
        ]);

        $validated['operator_id'] = $operator->id;

        OperatorSmsRate::updateOrCreate(
            ['operator_id' => $operator->id],
            $validated
        );

        return redirect()->route('panel.admin.operators.sms-rates')
            ->with('success', 'SMS rate assigned successfully.');
    }

    /**
     * Delete operator SMS rate.
     */
    public function deleteOperatorSmsRate(User $operator)
    {
        abort_unless($operator->isOperatorRole(), 403, 'User is not an operator.');

        OperatorSmsRate::where('operator_id', $operator->id)->delete();

        return redirect()->route('panel.admin.operators.sms-rates')
            ->with('success', 'SMS rate removed successfully.');
    }

    // ==================== NAS Device CRUD Methods ====================

    /**
     * Display NAS devices list.
     */
    public function nasList(): View
    {
        $devices = Nas::where('tenant_id', getCurrentTenantId())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panels.admin.nas.index', compact('devices'));
    }

    /**
     * Show create NAS form.
     */
    public function nasCreate(): View
    {
        return view('panels.admin.nas.create');
    }

    /**
     * Store new NAS device.
     */
    public function nasStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'nas_name' => 'required|string|max:100',
            'short_name' => 'required|string|max:50',
            'server' => 'required|ip|max:100|unique:nas,server',
            'secret' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'ports' => 'nullable|integer|min:0',
            'community' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $validated['tenant_id'] = getCurrentTenantId();

        Nas::create($validated);

        return redirect()->route('panel.admin.network.nas')
            ->with('success', 'NAS device created successfully.');
    }

    /**
     * Show NAS device details.
     */
    public function nasShow($id): View
    {
        $device = Nas::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        return view('panels.admin.nas.show', compact('device'));
    }

    /**
     * Show edit NAS form.
     */
    public function nasEdit($id): View
    {
        $device = Nas::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        return view('panels.admin.nas.edit', compact('device'));
    }

    /**
     * Update NAS device.
     */
    public function nasUpdate(Request $request, $id)
    {
        $device = Nas::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'nas_name' => 'required|string|max:100',
            'short_name' => 'required|string|max:50',
            'server' => 'required|ip|max:100|unique:nas,server,' . $id,
            'secret' => 'nullable|string|max:100',
            'type' => 'required|string|max:50',
            'ports' => 'nullable|integer|min:0',
            'community' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        // Preserve existing secret if the field was left empty in the update form
        if (array_key_exists('secret', $validated) && ($validated['secret'] === null || $validated['secret'] === '')) {
            unset($validated['secret']);
        }

        $device->update($validated);

        return redirect()->route('panel.admin.network.nas')
            ->with('success', 'NAS device updated successfully.');
    }

    /**
     * Delete NAS device.
     */
    public function nasDestroy($id)
    {
        $device = Nas::where('tenant_id', getCurrentTenantId())->findOrFail($id);
        $device->delete();

        return redirect()->route('panel.admin.network.nas')
            ->with('success', 'NAS device deleted successfully.');
    }

    /**
     * Test NAS connection.
     */
    public function nasTestConnection($id)
    {
        $device = Nas::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        // Validate server is a valid IP address before executing command
        if (! filter_var($device->server, FILTER_VALIDATE_IP)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IP address format',
            ], 400);
        }

        // Simple ping test with sanitized IP
        $output = [];
        $returnCode = 0;
        $sanitizedIp = escapeshellarg($device->server);
        exec("ping -c 1 -W 2 {$sanitizedIp}", $output, $returnCode);

        if ($returnCode === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Connection failed - Device unreachable',
        ], 500);
    }

    // ==================== OLT Device CRUD Methods ====================

    /**
     * Store new OLT device.
     */
    public function oltStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address',
            'model' => 'required|string|max:100',
            'snmp_version' => 'required|in:v1,v2c,v3',
            'snmp_community' => 'required_if:snmp_version,v1,v2c|nullable|string|max:255',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['tenant_id'] = getCurrentTenantId();

        Olt::create($validated);

        return redirect()->route('panel.admin.network.olt')
            ->with('success', 'OLT device created successfully.');
    }

    /**
     * Show OLT device details.
     */
    public function oltShow($id): View
    {
        $olt = Olt::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        return view('panels.admin.olt.show', compact('olt'));
    }

    /**
     * Show edit OLT form.
     */
    public function oltEdit($id): View
    {
        $olt = Olt::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        return view('panels.admin.olt.edit', compact('olt'));
    }

    /**
     * Update OLT device.
     */
    public function oltUpdate(Request $request, $id)
    {
        $olt = Olt::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address,' . $id,
            'model' => 'required|string|max:100',
            'snmp_version' => 'required|in:v1,v2c,v3',
            'snmp_community' => 'required_if:snmp_version,v1,v2c|nullable|string|max:255',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $olt->update($validated);

        return redirect()->route('panel.admin.network.olt')
            ->with('success', 'OLT device updated successfully.');
    }

    /**
     * Delete OLT device.
     */
    public function oltDestroy($id)
    {
        $olt = Olt::where('tenant_id', getCurrentTenantId())->findOrFail($id);
        $olt->delete();

        return redirect()->route('panel.admin.network.olt')
            ->with('success', 'OLT device deleted successfully.');
    }

    /**
     * Test OLT connection.
     */
    public function oltTestConnection($id)
    {
        $olt = Olt::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        // Validate IP address format before executing command
        if (! filter_var($olt->ip_address, FILTER_VALIDATE_IP)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IP address format',
            ], 400);
        }

        // Simple ping test with sanitized IP
        $output = [];
        $returnCode = 0;
        $sanitizedIp = escapeshellarg($olt->ip_address);
        exec("ping -c 1 -W 2 {$sanitizedIp}", $output, $returnCode);

        if ($returnCode === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Connection failed - Device unreachable',
        ], 500);
    }

    /**
     * Test router connection.
     */
    public function routerTestConnection($id)
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())->findOrFail($id);

        // Validate IP address format before executing command
        if (! filter_var($router->ip_address, FILTER_VALIDATE_IP)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IP address format',
            ], 400);
        }

        // Simple ping test with sanitized IP
        $output = [];
        $returnCode = 0;
        $sanitizedIp = escapeshellarg($router->ip_address);
        exec("ping -c 1 -W 2 {$sanitizedIp}", $output, $returnCode);

        if ($returnCode === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Connection failed - Device unreachable',
        ], 500);
    }

    // ==================== Prepaid Card Management Methods ====================

    /**
     * Display recharge cards list.
     */
    public function cardsIndex(): View
    {
        // Validate filters
        $validated = request()->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,used,expired,cancelled',
        ]);

        $query = \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())
            ->with(['generatedBy', 'assignedTo', 'usedBy']);

        // Apply filters
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('card_number', 'like', "%{$search}%")
                    ->orWhere('pin', 'like', "%{$search}%");
            });
        }

        $cards = $query->latest()->paginate(20);

        $stats = [
            'total_cards' => \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())->count(),
            'active_cards' => \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())->where('status', 'active')->count(),
            'used_cards' => \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())->where('status', 'used')->count(),
            'total_value' => \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())->where('status', 'active')->sum('denomination'),
        ];

        return view('panels.admin.cards.index', compact('cards', 'stats'));
    }

    /**
     * Show card generation form.
     */
    public function cardsCreate(): View
    {
        $operators = User::where('tenant_id', getCurrentTenantId())
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'operator');
            })
            ->get();

        return view('panels.admin.cards.create', compact('operators'));
    }

    /**
     * Generate cards.
     */
    public function cardsStore(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
            'denomination' => 'required|numeric|min:1',
            'expires_at' => 'nullable|date|after:today',
            'assign_to' => 'nullable|exists:users,id,tenant_id,' . getCurrentTenantId(),
        ]);

        $cardService = new \App\Services\CardDistributionService;
        
        $expiresAt = $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null;
        
        try {
            $cards = $cardService->generateCards(
                $validated['quantity'],
                $validated['denomination'],
                auth()->user(),
                $expiresAt
            );

            // Assign to operator if specified
            if (isset($validated['assign_to'])) {
                $cardIds = collect($cards)->pluck('id')->toArray();
                $distributor = User::where('tenant_id', getCurrentTenantId())
                    ->findOrFail($validated['assign_to']);
                $cardService->assignCardsToDistributor($cardIds, $distributor);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to generate or assign recharge cards.', [
                'error' => $e->getMessage(),
                'user_id' => optional(auth()->user())->id,
                'quantity' => $validated['quantity'] ?? null,
                'denomination' => $validated['denomination'] ?? null,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while generating the cards. Please try again.');
        }

        return redirect()->route('panel.admin.cards.index')
            ->with('success', count($cards) . ' cards generated successfully.');
    }

    /**
     * Export cards to PDF/Excel.
     */
    public function cardsExport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:pdf,excel',
            'card_ids' => 'required|array',
            'card_ids.*' => 'exists:recharge_cards,id',
        ]);

        $cards = \App\Models\RechargeCard::whereIn('id', $validated['card_ids'])
            ->where('tenant_id', getCurrentTenantId())
            ->get();

        if ($cards->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No cards found to export. Please ensure the selected cards belong to your organization.');
        }

        if ($validated['format'] === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('panels.admin.cards.export-pdf', compact('cards'));
            return $pdf->download('recharge-cards-' . now()->format('Y-m-d') . '.pdf');
        } else {
            // Excel export
            return Excel::download(
                new \App\Exports\RechargeCardsExport($cards),
                'recharge-cards-' . now()->format('Y-m-d') . '.xlsx'
            );
        }
    }

    /**
     * Show card details.
     */
    public function cardsShow($id): View
    {
        $card = \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())
            ->with(['generatedBy', 'assignedTo', 'usedBy'])
            ->findOrFail($id);

        return view('panels.admin.cards.show', compact('card'));
    }

    /**
     * Assign cards to distributor.
     */
    public function cardsAssign(Request $request)
    {
        $validated = $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'exists:recharge_cards,id',
            'distributor_id' => 'required|exists:users,id,tenant_id,' . getCurrentTenantId(),
        ]);

        $cardService = new \App\Services\CardDistributionService;
        $distributor = User::where('tenant_id', getCurrentTenantId())
            ->findOrFail($validated['distributor_id']);
        
        $assigned = $cardService->assignCardsToDistributor($validated['card_ids'], $distributor);

        return redirect()->back()
            ->with('success', $assigned . ' cards assigned to ' . $distributor->name);
    }

    /**
     * Show used cards mapping.
     */
    public function cardsUsedMapping(): View
    {
        $usedCards = \App\Models\RechargeCard::where('tenant_id', getCurrentTenantId())
            ->where('status', 'used')
            ->with(['usedBy', 'assignedTo'])
            ->latest('used_at')
            ->paginate(20);

        return view('panels.admin.cards.used-mapping', compact('usedCards'));
    }

    /**
     * Show IP Pool Analytics Dashboard
     */
    public function ipAnalytics(): View
    {
        $analytics = $this->getIpPoolAnalytics();
        $poolStats = $this->getPoolStats();
        $recentAllocations = $this->getRecentAllocations();

        return view('panels.admin.network.ip-pool-analytics', compact('analytics', 'poolStats', 'recentAllocations'));
    }

    /**
     * Export IP Analytics
     */
    public function exportIpAnalytics(Request $request)
    {
        $format = $request->get('format', 'pdf');
        
        // Gather analytics data
        $analytics = $this->getIpPoolAnalytics();
        $poolStats = $this->getPoolStats();
        $recentAllocations = $this->getRecentAllocations();
        
        $data = compact('analytics', 'poolStats', 'recentAllocations');
        
        switch ($format) {
            case 'pdf':
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.ip-analytics-pdf', $data);
                return $pdf->download('ip-pool-analytics-' . date('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(
                    new \App\Exports\IpAnalyticsExport($data),
                    'ip-pool-analytics-' . date('Y-m-d') . '.xlsx'
                );
                
            case 'csv':
                return Excel::download(
                    new \App\Exports\IpAnalyticsExport($data),
                    'ip-pool-analytics-' . date('Y-m-d') . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );
                
            default:
                abort(400, 'Invalid export format');
        }
    }

    /**
     * Get IP pool analytics data
     */
    protected function getIpPoolAnalytics(): array
    {
        $pools = IpPool::all();
        
        $totalIps = $pools->sum('total_ips');
        $allocatedIps = $pools->sum('used_ips');
        $availableIps = $totalIps - $allocatedIps;
        
        return [
            'total_ips' => $totalIps,
            'allocated_ips' => $allocatedIps,
            'available_ips' => $availableIps,
            'allocation_percent' => $totalIps > 0 ? ($allocatedIps / $totalIps) * 100 : 0,
            'available_percent' => $totalIps > 0 ? ($availableIps / $totalIps) * 100 : 0,
            'total_pools' => $pools->count(),
            'by_type' => $this->getPoolsByType($pools),
            'top_utilized' => $this->getTopUtilizedPools($pools),
        ];
    }

    /**
     * Get pool statistics
     */
    protected function getPoolStats(): array
    {
        $pools = IpPool::all();
        
        return $pools->map(function ($pool) {
            $totalIps = $pool->total_ips;
            $usedIps = $pool->used_ips;
            $availableIps = $totalIps - $usedIps;
            
            return [
                'name' => $pool->name,
                'description' => $pool->description,
                'start_ip' => $pool->start_ip,
                'end_ip' => $pool->end_ip,
                'gateway' => $pool->gateway,
                'total_ips' => $totalIps,
                'allocated_ips' => $usedIps,
                'available_ips' => $availableIps,
                'utilization_percent' => $pool->utilizationPercent(),
                'pool_type' => $pool->pool_type ?? 'standard',
            ];
        })->toArray();
    }

    /**
     * Get pools by type
     */
    protected function getPoolsByType($pools): array
    {
        $byType = [];
        
        foreach ($pools as $pool) {
            $type = $pool->pool_type ?? 'standard';
            
            if (!isset($byType[$type])) {
                $byType[$type] = [
                    'total' => 0,
                    'allocated' => 0,
                ];
            }
            
            $byType[$type]['total'] += $pool->total_ips;
            $byType[$type]['allocated'] += $pool->used_ips;
        }
        
        return $byType;
    }

    /**
     * Get top utilized pools
     */
    protected function getTopUtilizedPools($pools): array
    {
        return $pools->map(function ($pool) {
            return [
                'name' => $pool->name,
                'total' => $pool->total_ips,
                'allocated' => $pool->used_ips,
                'utilization' => $pool->utilizationPercent(),
            ];
        })
        ->sortByDesc('utilization')
        ->take(5)
        ->values()
        ->toArray();
    }

    /**
     * Get recent IP allocations
     */
    protected function getRecentAllocations(): array
    {
        $allocations = IpAllocation::with('subnet.pool')
            ->latest()
            ->take(20)
            ->get();
        
        return $allocations->map(function ($allocation) {
            // Get pool name from subnet relationship
            $poolName = 'N/A';
            if ($allocation->subnet && $allocation->subnet->pool) {
                $poolName = $allocation->subnet->pool->name;
            }
            
            return [
                'ip_address' => $allocation->ip_address,
                'pool_name' => $poolName,
                'assigned_to' => $allocation->username ?? 'N/A',
                'allocated_at' => $allocation->allocated_at ?? $allocation->created_at,
            ];
        })->toArray();
    }
}
