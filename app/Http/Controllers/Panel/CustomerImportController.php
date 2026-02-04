<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Events\ImportPppCustomersRequested;
use App\Http\Controllers\Controller;
use App\Models\CustomerImport;
use App\Models\MikrotikRouter;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerImportController extends Controller
{
    /**
     * Show import form.
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        // Get active routers (NAS functionality is now integrated into routers)
        $routers = MikrotikRouter::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();
            
        $packages = Package::where('tenant_id', $tenantId)->get();

        // Get recent imports
        $recentImports = CustomerImport::where('operator_id', auth()->id())
            ->with(['nas', 'router'])
            ->latest()
            ->take(10)
            ->get();

        return view('panels.isp.customers.pppoe-import', compact('routers', 'packages', 'recentImports'));
    }

    /**
     * Start import process.
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'router_id' => [
                'required',
                'integer',
                'exists:mikrotik_routers,id',
            ],
            'filter_disabled' => 'nullable|boolean',
            'generate_bills' => 'nullable|boolean',
            'package_id' => [
                'nullable',
                'integer',
                'exists:packages,id',
            ],
        ]);

        try {
            // Verify router belongs to tenant and is active (security check)
            $router = MikrotikRouter::where('id', $validated['router_id'])
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            if (!$router) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid router selected. Router must be active and belong to your tenant.',
                ], 422);
            }

            // Verify package belongs to tenant if provided (security check)
            if (!empty($validated['package_id'])) {
                $package = Package::where('id', $validated['package_id'])
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid package selected. Package must belong to your tenant.',
                    ], 422);
                }
            }

            // Check for duplicate import today
            $existingImport = CustomerImport::where('operator_id', auth()->id())
                ->whereDate('created_at', today())
                ->where('status', 'in_progress')
                ->where('router_id', $validated['router_id'])
                ->first();

            if ($existingImport) {
                return response()->json([
                    'success' => false,
                    'message' => 'An import is already in progress for this router today.',
                ], 422);
            }

            // Dispatch event (NAS functionality is now part of routers)
            event(new ImportPppCustomersRequested(
                auth()->id(),
                null, // nas_id deprecated
                [
                    'router_id' => $validated['router_id'],
                    'filter_disabled' => $validated['filter_disabled'] ?? true,
                    'generate_bills' => $validated['generate_bills'] ?? false,
                    'package_id' => $validated['package_id'] ?? null,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Import process has been started. You will be notified when it completes.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get import status.
     */
    public function status(int $importId): JsonResponse
    {
        $import = CustomerImport::where('operator_id', auth()->id())
            ->findOrFail($importId);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $import->status,
                'total' => $import->total_count,
                'success' => $import->success_count,
                'failed' => $import->failed_count,
                'progress' => $import->getProgressPercentage(),
                'errors' => $import->errors,
            ],
        ]);
    }

    /**
     * Get import history.
     */
    public function history(Request $request): JsonResponse
    {
        $imports = CustomerImport::where('operator_id', auth()->id())
            ->with(['nas', 'router'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $imports,
        ]);
    }
}
