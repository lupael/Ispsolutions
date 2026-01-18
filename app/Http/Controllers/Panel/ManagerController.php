<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\NetworkUserSession;
use Illuminate\View\View;

class ManagerController extends Controller
{
    /**
     * Display the manager dashboard.
     */
    public function dashboard(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_network_users' => NetworkUser::where('tenant_id', $tenantId)->count(),
            'active_sessions' => NetworkUserSession::where('tenant_id', $tenantId)
                ->whereNull('end_time')->count(),
            'pppoe_users' => NetworkUser::where('tenant_id', $tenantId)
                ->where('service_type', 'pppoe')->count(),
            'hotspot_users' => NetworkUser::where('tenant_id', $tenantId)
                ->where('service_type', 'hotspot')->count(),
        ];

        return view('panels.manager.dashboard', compact('stats'));
    }

    /**
     * Display network users listing.
     */
    public function networkUsers(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $networkUsers = NetworkUser::where('tenant_id', $tenantId)->latest()->paginate(20);

        return view('panels.manager.network-users.index', compact('networkUsers'));
    }

    /**
     * Display active sessions.
     */
    public function sessions(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $sessions = NetworkUserSession::where('tenant_id', $tenantId)
            ->whereNull('end_time')
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('panels.manager.sessions.index', compact('sessions'));
    }

    /**
     * Display reports.
     */
    public function reports(): View
    {
        return view('panels.manager.reports');
    }

    /**
     * Display customers (view-only based on permissions).
     */
    public function customers(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Managers can view operators' or sub-operators' customers based on permissions
        $customers = \App\Models\User::where('tenant_id', $tenantId)
            ->where('operator_level', 100)
            ->latest()
            ->paginate(20);

        return view('panels.manager.customers.index', compact('customers'));
    }

    /**
     * Display payments (if authorized).
     */
    public function payments(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $payments = \App\Models\Payment::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(20);

        return view('panels.manager.payments.index', compact('payments'));
    }

    /**
     * Display complaints (assigned department).
     */
    public function complaints(): View
    {
        // TODO: Implement ticket system filtering by department
        // For now, return empty paginated collection to prevent blade errors
        $complaints = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.manager.complaints.index', compact('complaints'));
    }
}
