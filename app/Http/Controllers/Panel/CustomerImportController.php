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
        $routers = MikrotikRouter::where('tenant_id', $tenantId)->get();
        $nasDevices = Nas::where('tenant_id', $tenantId)->get();
        $packages = Package::where('tenant_id', $tenantId)->get();

        // Get recent imports
        $recentImports = CustomerImport::where('operator_id', auth()->id())
            ->with(['nas'])
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
            'nas_id' => 'required|integer|exists:nas,id',
            'filter_disabled' => 'nullable|boolean',
            'generate_bills' => 'nullable|boolean',
            'package_id' => 'nullable|integer|exists:packages,id',
        ]);

        try {
            // Check for duplicate import today
            $existingImport = CustomerImport::where('operator_id', auth()->id())
                ->where('nas_id', $validated['nas_id'])
                ->whereDate('created_at', today())
                ->where('status', 'in_progress')
                ->first();

            if ($existingImport) {
                return response()->json([
                    'success' => false,
                    'message' => 'An import is already in progress for this NAS today.',
                ], 422);
            }

            // Dispatch event
            event(new ImportPppCustomersRequested(
                auth()->id(),
                $validated['nas_id'],
                [
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
            ->with(['nas'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $imports,
        ]);
    }
}
