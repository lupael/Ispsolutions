<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Events\ImportPppCustomersRequested;
use App\Http\Controllers\Controller;
use App\Models\CustomerImport;
use App\Models\MikrotikRouter;
use App\Models\Nas;
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
        
        // Get active routers, including those with active NAS or no NAS
        $routers = MikrotikRouter::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function($query) {
                // Include routers with no NAS, or routers whose NAS is also active
                $query->whereNull('nas_id')
                    ->orWhereHas('nas', function($q) {
                        $q->where('status', 'active');
                    });
            })
            ->get();
            
        $nasDevices = Nas::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();
        $packages = Package::where('tenant_id', $tenantId)->get();

        // Get recent imports
        $recentImports = CustomerImport::where('operator_id', auth()->id())
            ->with(['nas', 'router'])
            ->latest()
            ->take(10)
            ->get();

        return view('panels.admin.customers.pppoe-import', compact('routers', 'nasDevices', 'packages', 'recentImports'));
    }

    /**
     * Start import process.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nas_id' => 'nullable|integer|exists:nas,id',
            'router_id' => 'nullable|integer|exists:mikrotik_routers,id',
            'filter_disabled' => 'nullable|boolean',
            'generate_bills' => 'nullable|boolean',
            'package_id' => 'nullable|integer|exists:packages,id',
        ]);

        // Ensure either nas_id or router_id is provided
        if (empty($validated['nas_id']) && empty($validated['router_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either NAS device or Mikrotik router must be selected.',
            ], 422);
        }

        // Ensure nas_id and router_id are not both provided simultaneously
        if (! empty($validated['nas_id']) && ! empty($validated['router_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please select either a NAS device or a Mikrotik router, not both.',
            ], 422);
        }

        try {
            // Determine which ID to use for duplicate check
            $deviceId = $validated['nas_id'] ?? $validated['router_id'];
            $deviceType = isset($validated['nas_id']) ? 'nas' : 'router';

            // Check for duplicate import today
            $query = CustomerImport::where('operator_id', auth()->id())
                ->whereDate('created_at', today())
                ->where('status', 'in_progress');

            if ($deviceType === 'nas') {
                $query->where('nas_id', $deviceId);
            } else {
                $query->where('router_id', $deviceId);
            }

            $existingImport = $query->first();

            if ($existingImport) {
                return response()->json([
                    'success' => false,
                    'message' => 'An import is already in progress for this device today.',
                ], 422);
            }

            // Dispatch event
            event(new ImportPppCustomersRequested(
                auth()->id(),
                $validated['nas_id'] ?? null,
                [
                    'router_id' => $validated['router_id'] ?? null,
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
