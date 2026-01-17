<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Models\CiscoDevice;
use App\Models\Olt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display the staff dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'assigned_users' => NetworkUser::count(),
            'pending_tickets' => 0, // To be implemented
        ];

        // Eager load roles to avoid N+1 queries
        $user = auth()->user()->load('roles');
        
        // Check permissions and add device stats
        $canViewMikrotik = $user->hasPermission('devices.mikrotik.view');
        $canViewNas = $user->hasPermission('devices.nas.view');
        $canViewCisco = $user->hasPermission('devices.cisco.view');
        $canViewOlt = $user->hasPermission('devices.olt.view');
        
        if ($canViewMikrotik) {
            $stats['total_mikrotik'] = MikrotikRouter::count();
        }
        if ($canViewNas) {
            $stats['total_nas'] = Nas::count();
        }
        if ($canViewCisco) {
            $stats['total_cisco'] = CiscoDevice::count();
        }
        if ($canViewOlt) {
            $stats['total_olt'] = Olt::count();
        }

        // Pass permission flags to view to avoid N+1 queries
        return view('panels.staff.dashboard', compact('stats', 'canViewMikrotik', 'canViewNas', 'canViewCisco', 'canViewOlt'));
    }

    /**
     * Display network users listing.
     */
    public function networkUsers(): View
    {
        $networkUsers = NetworkUser::latest()->paginate(20);

        return view('panels.staff.network-users.index', compact('networkUsers'));
    }

    /**
     * Display tickets listing.
     */
    public function tickets(): View
    {
        // To be implemented with ticket system
        return view('panels.staff.tickets.index');
    }

    /**
     * Display MikroTik routers listing (if permitted).
     */
    public function mikrotikRouters(): View
    {
        if (!auth()->user()->hasPermission('devices.mikrotik.view')) {
            abort(403, 'Unauthorized access to MikroTik routers.');
        }

        $routers = MikrotikRouter::latest()->paginate(20);

        return view('panels.staff.mikrotik.index', compact('routers'));
    }

    /**
     * Display NAS devices listing (if permitted).
     */
    public function nasDevices(): View
    {
        if (!auth()->user()->hasPermission('devices.nas.view')) {
            abort(403, 'Unauthorized access to NAS devices.');
        }

        $devices = Nas::latest()->paginate(20);

        return view('panels.staff.nas.index', compact('devices'));
    }

    /**
     * Display Cisco devices listing (if permitted).
     */
    public function ciscoDevices(): View
    {
        if (!auth()->user()->hasPermission('devices.cisco.view')) {
            abort(403, 'Unauthorized access to Cisco devices.');
        }

        $devices = CiscoDevice::latest()->paginate(20);

        return view('panels.staff.cisco.index', compact('devices'));
    }

    /**
     * Display OLT devices listing (if permitted).
     */
    public function oltDevices(): View
    {
        if (!auth()->user()->hasPermission('devices.olt.view')) {
            abort(403, 'Unauthorized access to OLT devices.');
        }

        $devices = Olt::latest()->paginate(20);

        return view('panels.staff.olt.index', compact('devices'));
    }
}
