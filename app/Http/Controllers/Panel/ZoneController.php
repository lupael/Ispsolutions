<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ZoneController extends Controller
{
    /**
     * Display zones listing.
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $zones = Zone::where('tenant_id', $tenantId)
            ->with(['parent', 'children'])
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
        $tenantId = auth()->user()->tenant_id;

        $parentZones = Zone::where('tenant_id', $tenantId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('panels.admin.zones.create', compact('parentZones'));
    }

    /**
     * Store a newly created zone.
     */
    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('zones')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                Rule::exists('zones', 'id')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:7',
            'coverage_type' => 'required|in:point,radius,polygon',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $tenantId;

        Zone::create($validated);

        return redirect()
            ->route('panel.admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(Zone $zone): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Ensure zone belongs to current tenant
        if ($zone->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to zone');
        }

        $parentZones = Zone::where('tenant_id', $tenantId)
            ->active()
            ->where('id', '!=', $zone->id)
            ->orderBy('name')
            ->get();

        // Filter out descendants to prevent circular references
        $parentZones = $parentZones->filter(function ($parentZone) use ($zone) {
            return ! $this->isDescendant($zone, $parentZone);
        });

        return view('panels.admin.zones.edit', compact('zone', 'parentZones'));
    }

    /**
     * Check if a zone is a descendant of another zone
     */
    private function isDescendant(Zone $ancestor, Zone $potentialDescendant): bool
    {
        $current = $potentialDescendant;
        $visited = [];

        while ($current && $current->parent_id) {
            if ($current->parent_id === $ancestor->id) {
                return true;
            }

            // Prevent infinite loop in case of circular references
            if (in_array($current->parent_id, $visited)) {
                break;
            }

            $visited[] = $current->parent_id;
            $current = Zone::find($current->parent_id);
        }

        return false;
    }

    /**
     * Update the specified zone.
     */
    public function update(Request $request, Zone $zone): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        // Ensure zone belongs to current tenant
        if ($zone->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to zone');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('zones')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })->ignore($zone->id),
            ],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                Rule::exists('zones', 'id')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
                function ($attribute, $value, $fail) use ($zone) {
                    if ($value == $zone->id) {
                        $fail('A zone cannot be its own parent.');
                    }

                    // Check for circular references
                    if ($value && $this->wouldCreateCircularReference($zone, $value)) {
                        $fail('This parent assignment would create a circular reference.');
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
            ->route('panel.admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    /**
     * Check if setting a parent would create a circular reference
     */
    private function wouldCreateCircularReference(Zone $zone, int $newParentId): bool
    {
        $currentZone = Zone::find($newParentId);
        $visited = [];

        while ($currentZone) {
            if ($currentZone->id === $zone->id) {
                return true;
            }

            // Prevent infinite loop
            if (in_array($currentZone->id, $visited)) {
                break;
            }

            $visited[] = $currentZone->id;
            $currentZone = $currentZone->parent;
        }

        return false;
    }

    /**
     * Remove the specified zone.
     */
    public function destroy(Zone $zone): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        // Ensure zone belongs to current tenant
        if ($zone->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to zone');
        }

        // Check if zone has customers
        $customerCount = $zone->customers()->count();

        if ($customerCount > 0) {
            return redirect()
                ->route('panel.admin.zones.index')
                ->with('error', "Cannot delete zone with {$customerCount} customers. Please reassign customers first.");
        }

        // Check if zone has children
        if ($zone->children()->count() > 0) {
            return redirect()
                ->route('panel.admin.zones.index')
                ->with('error', 'Cannot delete zone with child zones. Please delete or reassign child zones first.');
        }

        $zone->delete();

        return redirect()
            ->route('panel.admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }

    /**
     * Display zone details and statistics.
     */
    public function show(Zone $zone): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Ensure zone belongs to current tenant
        if ($zone->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to zone');
        }

        $zone->load(['parent', 'children']);

        $stats = [
            'total_customers' => $zone->customers()->count(),
            'active_customers' => $zone->customers()->where('is_active', true)->count(),
            'total_network_users' => $zone->networkUsers()->count(),
            'active_network_users' => $zone->networkUsers()->where('status', 'active')->count(),
            'child_zones' => $zone->children()->count(),
        ];

        return view('panels.admin.zones.show', compact('zone', 'stats'));
    }

    /**
     * Display zone-based analytics report.
     * Optimized with single query to avoid N+1
     */
    public function report(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $zones = Zone::where('tenant_id', $tenantId)
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
     * Added tenant and zone validation
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => [
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'zone_id' => [
                'required',
                Rule::exists('zones', 'id')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
        ]);

        // Verify customers belong to tenant before updating
        User::whereIn('id', $validated['customer_ids'])
            ->where('tenant_id', $tenantId)
            ->update(['zone_id' => $validated['zone_id']]);

        return redirect()
            ->back()
            ->with('success', count($validated['customer_ids']) . ' customers assigned to zone successfully.');
    }
}
