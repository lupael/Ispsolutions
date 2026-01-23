<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\User;
use App\Models\NetworkUser;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ZoneController extends Controller
{
    /**
     * Display zones listing.
     */
    public function index(): View
    {
        $zones = Zone::with(['parent', 'children'])
            ->withCount('customers')
            ->orderBy('name')
            ->paginate(20);

        return view('panels.admin.zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create(): View
    {
        $parentZones = Zone::active()
            ->whereNull('parent_id')
            ->orWhereHas('parent')
            ->orderBy('name')
            ->get();

        return view('panels.admin.zones.create', compact('parentZones'));
    }

    /**
     * Store a newly created zone.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:zones,code',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:zones,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'coverage_type' => 'required|in:point,radius,polygon',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        Zone::create($validated);

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(Zone $zone): View
    {
        $parentZones = Zone::active()
            ->where('id', '!=', $zone->id)
            ->whereNull('parent_id')
            ->orWhereHas('parent')
            ->orderBy('name')
            ->get();

        return view('panels.admin.zones.edit', compact('zone', 'parentZones'));
    }

    /**
     * Update the specified zone.
     */
    public function update(Request $request, Zone $zone): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('zones')->ignore($zone->id)],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:zones,id',
                function ($attribute, $value, $fail) use ($zone) {
                    if ($value == $zone->id) {
                        $fail('A zone cannot be its own parent.');
                    }
                },
            ],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'coverage_type' => 'required|in:point,radius,polygon',
            'is_active' => 'boolean',
        ]);

        $zone->update($validated);

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    /**
     * Remove the specified zone.
     */
    public function destroy(Zone $zone): RedirectResponse
    {
        // Check if zone has customers
        $customerCount = $zone->customers()->count();
        
        if ($customerCount > 0) {
            return redirect()
                ->route('admin.zones.index')
                ->with('error', "Cannot delete zone with {$customerCount} customers. Please reassign customers first.");
        }

        // Check if zone has children
        if ($zone->children()->count() > 0) {
            return redirect()
                ->route('admin.zones.index')
                ->with('error', 'Cannot delete zone with child zones. Please delete or reassign child zones first.');
        }

        $zone->delete();

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }

    /**
     * Display zone details and statistics.
     */
    public function show(Zone $zone): View
    {
        $zone->load(['parent', 'children', 'customers']);
        
        $stats = [
            'total_customers' => $zone->customers()->count(),
            'active_customers' => $zone->customers()->where('is_active', true)->count(),
            'total_network_users' => $zone->networkUsers()->count(),
            'active_network_users' => $zone->networkUsers()->where('is_active', true)->count(),
            'child_zones' => $zone->children()->count(),
        ];

        return view('panels.admin.zones.show', compact('zone', 'stats'));
    }

    /**
     * Display zone-based analytics report.
     */
    public function report(): View
    {
        $zones = Zone::with(['customers', 'networkUsers'])
            ->withCount([
                'customers',
                'customers as active_customers_count' => function ($query) {
                    $query->where('is_active', true);
                },
                'networkUsers',
                'networkUsers as active_network_users_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->orderBy('name')
            ->get();

        $totalStats = [
            'total_zones' => $zones->count(),
            'active_zones' => $zones->where('is_active', true)->count(),
            'total_customers' => $zones->sum('customers_count'),
            'active_customers' => $zones->sum('active_customers_count'),
            'total_network_users' => $zones->sum('network_users_count'),
            'active_network_users' => $zones->sum('active_network_users_count'),
        ];

        return view('panels.admin.zones.report', compact('zones', 'totalStats'));
    }

    /**
     * Assign customers to zones (bulk operation).
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
            'zone_id' => 'required|exists:zones,id',
        ]);

        User::whereIn('id', $validated['customer_ids'])
            ->update(['zone_id' => $validated['zone_id']]);

        return redirect()
            ->back()
            ->with('success', count($validated['customer_ids']) . ' customers assigned to zone successfully.');
    }
}
