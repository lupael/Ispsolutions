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
}
