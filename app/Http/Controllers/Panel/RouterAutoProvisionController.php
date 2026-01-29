<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Services\MikrotikAutoProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Router Auto-Provisioning Controller
 *
 * Handles automated provisioning of Mikrotik routers on first connect.
 */
class RouterAutoProvisionController extends Controller
{
    public function __construct(
        private MikrotikAutoProvisioningService $provisioningService
    ) {}

    /**
     * Execute auto-provisioning for a router.
     */
    public function provision(Request $request, int $routerId): JsonResponse
    {
        $validated = $request->validate([
            'radius_server' => 'nullable|ip',
        ]);

        try {
            $router = MikrotikRouter::findOrFail($routerId);

            $result = $this->provisioningService->provisionOnFirstConnect(
                $router,
                $validated['radius_server'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'steps' => $result['steps'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'steps' => $result['steps'],
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error during auto-provisioning', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Auto-provisioning failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if router has been provisioned.
     */
    public function status(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::findOrFail($routerId);

            $isProvisioned = $this->provisioningService->isProvisioned($router);

            return response()->json([
                'success' => true,
                'provisioned' => $isProvisioned,
                'router' => [
                    'id' => $router->id,
                    'name' => $router->name,
                    'api_status' => $router->api_status,
                    'has_nas' => (bool) $router->nas_id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking provisioning status', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check provisioning status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
