<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IpPool;
use App\Models\MikrotikRouter;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use Illuminate\Http\Request;

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
        $routers = MikrotikRouter::all();
        $ipPools = IpPool::where('status', 'active')->get();

        return view('panels.admin.packages.mappings.create', compact('package', 'routers', 'ipPools'));
    }

    /**
     * Store a new mapping.
     */
    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
            'profile_name' => 'required|string|max:255',
            'speed_control_method' => 'nullable|string|max:255',
            'ip_pool_id' => 'nullable|exists:ip_pools,id',
        ]);

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
        $routers = MikrotikRouter::all();
        $ipPools = IpPool::where('status', 'active')->get();

        return view('panels.admin.packages.mappings.edit', compact('package', 'mapping', 'routers', 'ipPools'));
    }

    /**
     * Update a mapping.
     */
    public function update(Request $request, Package $package, PackageProfileMapping $mapping)
    {
        $validated = $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
            'profile_name' => 'required|string|max:255',
            'speed_control_method' => 'nullable|string|max:255',
            'ip_pool_id' => 'nullable|exists:ip_pools,id',
        ]);

        $mapping->update($validated);

        return redirect()->route('panel.admin.packages.mappings.index', $package)
            ->with('success', 'Mapping updated successfully.');
    }

    /**
     * Delete a mapping.
     */
    public function destroy(Package $package, PackageProfileMapping $mapping)
    {
        $mapping->delete();

        return redirect()->route('panel.admin.packages.mappings.index', $package)
            ->with('success', 'Mapping deleted successfully.');
    }
}
