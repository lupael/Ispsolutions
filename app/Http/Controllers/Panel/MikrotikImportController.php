<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Services\MikrotikImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MikrotikImportController extends Controller
{
    protected MikrotikImportService $importService;

    public function __construct(MikrotikImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show import form.
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $routers = MikrotikRouter::where('tenant_id', $tenantId)->get();
        $nasDevices = Nas::where('tenant_id', $tenantId)->get();

        return view('panels.admin.mikrotik.import', compact('routers', 'nasDevices'));
    }

    /**
     * Import IP pools.
     */
    public function importIpPools(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pools' => 'required|array',
            'pools.*.name' => 'required|string',
            'pools.*.ip_range' => 'required|string',
            'pools.*.subnet_mask' => 'nullable|string',
            'pools.*.gateway' => 'nullable|string',
            'pools.*.pool_type' => 'nullable|string',
            'pools.*.nas_id' => 'nullable|integer|exists:nas,id',
        ]);

        try {
            $result = $this->importService->importIpPools($validated);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? "Successfully imported {$result['imported']} IP addresses"
                    : 'Import failed',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import PPP profiles from router.
     */
    public function importProfiles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'router_id' => 'required|integer|exists:mikrotik_routers,id',
        ]);

        try {
            $result = $this->importService->importPppProfiles($validated['router_id']);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? "Successfully imported {$result['imported']} profiles"
                    : 'Import failed',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import PPP secrets (customers) from router.
     */
    public function importSecrets(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'router_id' => 'required|integer|exists:mikrotik_routers,id',
            'filter_disabled' => 'nullable|boolean',
            'generate_bills' => 'nullable|boolean',
        ]);

        try {
            $options = [
                'filter_disabled' => $validated['filter_disabled'] ?? true,
                'generate_bills' => $validated['generate_bills'] ?? false,
            ];

            $result = $this->importService->importPppSecrets($validated['router_id'], $options);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? "Successfully imported {$result['imported']} customers"
                    : 'Import failed',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate import data before actual import.
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:pools,profiles,secrets',
            'router_id' => 'required_if:type,profiles,secrets|integer|exists:mikrotik_routers,id',
            'pools' => 'required_if:type,pools|array',
        ]);

        try {
            $validation = [
                'valid' => true,
                'warnings' => [],
                'errors' => [],
            ];

            // Validate based on type
            if ($validated['type'] === 'pools') {
                // Check for duplicate IPs
                foreach ($validated['pools'] as $pool) {
                    $ips = $this->importService->parseIpRange($pool['ip_range']);
                    // Check duplicates in database
                    // Add validation logic here
                }
            }

            return response()->json([
                'success' => true,
                'data' => $validation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
