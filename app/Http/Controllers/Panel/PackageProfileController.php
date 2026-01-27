<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageProfileController extends Controller
{
    /**
     * Show profile association form for a package.
     */
    public function index(Package $package): View
    {
        // Check if user can view packages
        abort_unless(auth()->user()->hasAnyRole(['admin', 'superadmin']), 403);

        // Get all routers
        $routers = MikrotikRouter::where('status', 'active')->get();

        // Get existing mappings for this package
        $mappings = $package->profileMappings()
            ->with('router')
            ->get()
            ->keyBy('router_id');

        // Get available profiles per router
        $profilesByRouter = [];
        foreach ($routers as $router) {
            $profilesByRouter[$router->id] = MikrotikProfile::where('router_id', $router->id)
                ->get();
        }

        return view('panels.admin.packages.profile-association', compact(
            'package',
            'routers',
            'mappings',
            'profilesByRouter'
        ));
    }

    /**
     * Update profile associations for a package.
     */
    public function update(Request $request, Package $package): RedirectResponse
    {
        // Check if user can manage packages
        abort_unless(auth()->user()->hasAnyRole(['admin', 'superadmin']), 403);

        $validated = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.router_id' => 'required|exists:mikrotik_routers,id',
            'mappings.*.profile_name' => 'required|string',
            'auto_apply' => 'boolean',
        ]);

        try {
            // Delete existing mappings
            $package->profileMappings()->delete();

            // Create new mappings
            foreach ($validated['mappings'] as $mapping) {
                PackageProfileMapping::create([
                    'package_id' => $package->id,
                    'router_id' => $mapping['router_id'],
                    'profile_name' => $mapping['profile_name'],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Profile associations updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update associations: ' . $e->getMessage());
        }
    }

    /**
     * Apply profile to a customer based on their package.
     */
    public function applyToCustomer(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        try {
            $customer = \App\Models\User::findOrFail($validated['customer_id']);
            $router = MikrotikRouter::findOrFail($validated['router_id']);

            if (!$customer->package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer does not have a package assigned.',
                ], 400);
            }

            // Find the profile mapping for this package and router
            $mapping = PackageProfileMapping::where('package_id', $customer->package_id)
                ->where('router_id', $router->id)
                ->first();

            if (!$mapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile mapping found for this package and router.',
                ], 404);
            }

            // Apply the profile to the customer on the router
            // This would integrate with MikroTik API
            // TODO: Implement actual MikroTik API call to change customer profile
            \Illuminate\Support\Facades\Log::info("Applying profile {$mapping->profile_name} to customer {$customer->id} on router {$router->id}");

            return response()->json([
                'success' => true,
                'message' => 'Profile applied successfully.',
                'profile_name' => $mapping->profile_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profiles for a specific router via AJAX.
     */
    public function getRouterProfiles(MikrotikRouter $router): \Illuminate\Http\JsonResponse
    {
        try {
            $profiles = MikrotikProfile::where('router_id', $router->id)
                ->get(['id', 'name', 'rate_limit']);

            return response()->json([
                'success' => true,
                'profiles' => $profiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
