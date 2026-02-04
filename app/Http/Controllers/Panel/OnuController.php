<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\NetworkUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnuController extends Controller
{
    /**
     * Maximum number of network users to load in edit form
     * For large deployments, consider implementing a searchable dropdown
     */
    private const MAX_NETWORK_USERS_LIMIT = 100;

    /**
     * Display a listing of ONUs.
     */
    public function index(Request $request): View
    {
        $query = Onu::with(['olt', 'networkUser']);

        // Filter by OLT
        if ($request->filled('olt_id')) {
            $query->where('olt_id', $request->olt_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by serial number or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%");
            });
        }

        $onus = $query->orderBy('olt_id')->orderBy('pon_port')->orderBy('onu_id')->paginate(20);
        $olts = Olt::orderBy('name')->get();

        $stats = [
            'total' => Onu::count(),
            'online' => Onu::where('status', 'online')->count(),
            'offline' => Onu::where('status', 'offline')->count(),
        ];

        return view('panels.isp.onu.index', compact('onus', 'olts', 'stats'));
    }

    /**
     * Display the specified ONU.
     */
    public function show(Onu $onu): View
    {
        $onu->load(['olt', 'networkUser']);

        return view('panels.isp.onu.show', compact('onu'));
    }

    /**
     * Show the form for editing the specified ONU.
     */
    public function edit(Onu $onu): View
    {
        $onu->load(['olt', 'networkUser']);

        // Load a limited set of network users for better performance
        $networkUsers = NetworkUser::orderBy('username')
            ->limit(self::MAX_NETWORK_USERS_LIMIT)
            ->get();

        return view('panels.isp.onu.edit', compact('onu', 'networkUsers'));
    }

    /**
     * Update the specified ONU in storage.
     */
    public function update(Request $request, Onu $onu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'network_user_id' => 'nullable|exists:network_users,id',
        ]);

        $onu->update($validated);

        return redirect()->route('panel.isp.network.onu.show', $onu)
            ->with('success', 'ONU updated successfully.');
    }

    /**
     * Remove the specified ONU from storage.
     */
    public function destroy(Onu $onu): RedirectResponse
    {
        $onu->delete();

        return redirect()->route('panel.isp.network.onu.index')
            ->with('success', 'ONU deleted successfully.');
    }
}
