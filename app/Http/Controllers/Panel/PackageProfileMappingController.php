<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IpPool;
use App\Models\MikrotikRouter;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageProfileMappingController extends Controller
{
    /**
     * Display package mappings.
     */
    public function index(Package $package)
    {
        $mappings = PackageProfileMapping::with(['router', 'ipPool'])
            ->where('package_id', $package->id)
            ->get();

        return view('panels.admin.packages.mappings.index', compact('package', 'mappings'));
    }

    /**
     * Show form for creating a new mapping.
     */
    public function create(Package $package)
    {
        $user = auth()->user();
        $routers = MikrotikRouter::where('tenant_id', $user->tenant_id)->get();
        $ipPools = IpPool::where('tenant_id', $user->tenant_id)->where('status', 'active')->get();

        return view('panels.admin.packages.mappings.create', compact('package', 'routers', 'ipPools'));
    }

    /**
     * Store a new mapping.
     */
    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'router_id' => [
                'required',
                'exists:mikrotik_routers,id',
                Rule::unique('package_profile_mappings')->where(function ($query) use ($package) {
                    return $query->where('package_id', $package->id);
                }),
            ],
            'profile_name' => 'required|string|max:255',
            'speed_control_method' => ['nullable', 'string', Rule::in(['simple_queue', 'pcq', 'burst', ''])],
            'ip_pool_id' => 'nullable|exists:ip_pools,id',
        ]);

        // Normalize empty speed control method to null
        if (empty($validated['speed_control_method'])) {
            $validated['speed_control_method'] = null;
        }

        $validated['package_id'] = $package->id;

        PackageProfileMapping::create($validated);

        return redirect()->route('panel.admin.packages.mappings.index', $package)
            ->with('success', 'Mapping created successfully.');
    }

    /**
     * Show form for editing a mapping.
     */
    public function edit(Package $package, PackageProfileMapping $mapping)
    {
        // Ensure mapping belongs to package (scoped binding should handle this, but double check)
        if ($mapping->package_id !== $package->id) {
            abort(404);
        }

        $user = auth()->user();
        $routers = MikrotikRouter::where('tenant_id', $user->tenant_id)->get();
        $ipPools = IpPool::where('tenant_id', $user->tenant_id)->where('status', 'active')->get();

        return view('panels.admin.packages.mappings.create', compact('package', 'mapping', 'routers', 'ipPools'));
    }

    /**
     * Update a mapping.
     */
    public function update(Request $request, Package $package, PackageProfileMapping $mapping)
    {
        // Ensure mapping belongs to package
        if ($mapping->package_id !== $package->id) {
            abort(404);
        }

        $validated = $request->validate([
            'router_id' => [
                'required',
                'exists:mikrotik_routers,id',
                Rule::unique('package_profile_mappings')->where(function ($query) use ($package) {
                    return $query->where('package_id', $package->id);
                })->ignore($mapping->id),
            ],
            'profile_name' => 'required|string|max:255',
            'speed_control_method' => ['nullable', 'string', Rule::in(['simple_queue', 'pcq', 'burst', ''])],
            'ip_pool_id' => 'nullable|exists:ip_pools,id',
        ]);

        // Normalize empty speed control method to null
        if (empty($validated['speed_control_method'])) {
            $validated['speed_control_method'] = null;
        }

        $mapping->update($validated);

        return redirect()->route('panel.admin.packages.mappings.index', $package)
            ->with('success', 'Mapping updated successfully.');
    }

    /**
     * Delete a mapping.
     */
    public function destroy(Package $package, PackageProfileMapping $mapping)
    {
        // Ensure mapping belongs to package
        if ($mapping->package_id !== $package->id) {
            abort(404);
        }

        $mapping->delete();

        return redirect()->route('panel.admin.packages.mappings.index', $package)
            ->with('success', 'Mapping deleted successfully.');
    }
}
