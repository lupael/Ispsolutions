<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Services\MikrotikApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * NAS Netwatch Controller
 *
 * Implements RADIUS health monitoring and fallback automation
 * following IspBills pattern for netwatch management.
 *
 * This controller manages the netwatch configuration on Mikrotik routers
 * to ensure continuous operation even when the RADIUS server is down.
 */
class NasNetwatchController extends Controller
{
    public function __construct(
        private readonly MikrotikApiService $apiService
    ) {}

    /**
     * Display netwatch configuration for a router.
     */
    public function index(int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        // Get current netwatch configuration
        $netwatchConfig = $this->getNetwatchStatus($router);

        return response()->json([
            'success' => true,
            'router' => $router,
            'netwatchConfig' => $netwatchConfig,
        ]);
    }

    /**
     * Configure netwatch for RADIUS health monitoring (IspBills pattern).
     *
     * Implements the logic:
     * - RADIUS UP: Disable local secrets, drop non-radius sessions
     * - RADIUS DOWN: Enable local secrets for fallback
     */
    public function configure(Request $request, int $routerId): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'interval' => 'nullable|string',
            'timeout' => 'nullable|string',
        ]);

        try {
            $router = MikrotikRouter::with('nas')
                ->where('tenant_id', getCurrentTenantId())
                ->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device. Please configure NAS first.',
                ], 400);
            }

            if (! $validated['enabled']) {
                // Remove netwatch configuration
                return $this->removeNetwatch($router);
            }

            // Configure netwatch with RADIUS health monitoring
            $result = $this->configureNetwatchForRadius($router, $validated);

            if ($result['success']) {
                Log::info('Netwatch configured successfully', [
                    'router_id' => $router->id,
                    'nas_id' => $router->nas->id,
                    'radius_server' => $router->nas->server,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'RADIUS health monitoring configured successfully',
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to configure netwatch',
                'errors' => $result['errors'] ?? [],
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error configuring netwatch', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while configuring netwatch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current netwatch status for a router.
     */
    public function status(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')
                ->where('tenant_id', getCurrentTenantId())
                ->findOrFail($routerId);
            $status = $this->getNetwatchStatus($router);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting netwatch status', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get netwatch status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove netwatch configuration from a router.
     */
    public function remove(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')
                ->where('tenant_id', getCurrentTenantId())
                ->findOrFail($routerId);

            return $this->removeNetwatch($router);
        } catch (\Exception $e) {
            Log::error('Error removing netwatch', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test netwatch functionality.
     */
    public function test(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')
                ->where('tenant_id', getCurrentTenantId())
                ->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device',
                ], 400);
            }

            $radiusServer = config('radius.server_ip', '127.0.0.1');
            $menu = 'tool/netwatch';

            // Get netwatch entries
            $netwatchEntries = $this->apiService->getMktRows($router, $menu);

            // Find the RADIUS netwatch entry
            $radiusNetwatch = collect($netwatchEntries)->first(function ($entry) use ($radiusServer) {
                return isset($entry['host']) && $entry['host'] === $radiusServer;
            });

            if (! $radiusNetwatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No netwatch entry found for RADIUS server',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Netwatch is configured and monitoring RADIUS server',
                'data' => [
                    'host' => $radiusNetwatch['host'] ?? null,
                    'status' => $radiusNetwatch['status'] ?? 'unknown',
                    'since' => $radiusNetwatch['since'] ?? null,
                    'interval' => $radiusNetwatch['interval'] ?? null,
                    'timeout' => $radiusNetwatch['timeout'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error testing netwatch', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to test netwatch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configure netwatch for RADIUS health monitoring (IspBills pattern).
     *
     * Implements the logic:
     * - RADIUS UP: Force RADIUS authentication (disable local secrets, drop non-radius sessions)
     * - RADIUS DOWN: Enable local secrets as fallback
     *
     * This ensures users can still authenticate locally when RADIUS is down.
     */
    private function configureNetwatchForRadius(MikrotikRouter $router, array $config): array
    {
        try {
            // Use RADIUS server IP from config; do not rely on NAS server (router IP)
            $radiusServer = config('radius.server_ip', '127.0.0.1');
            $interval = $config['interval'] ?? '1m';
            $timeout = $config['timeout'] ?? '1s';

            // Scripts as defined in IspBills pattern
            // UP script: RADIUS is working, force all auth through RADIUS
            // - Disable local secrets (they should not be used when RADIUS works)
            // - Remove any active non-RADIUS sessions (force re-auth through RADIUS)
            $upScript = "/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];";

            // DOWN script: RADIUS is down, enable local secrets as fallback
            // - Enable all disabled secrets so users can authenticate locally
            $downScript = "/ppp secret enable [find disabled=yes];";

            $menu = 'tool/netwatch';
            $netwatchRow = [
                'host' => $radiusServer,
                'interval' => $interval,
                'timeout' => $timeout,
                'up-script' => $upScript,
                'down-script' => $downScript,
                'comment' => 'radius',
            ];

            // Remove any existing netwatch for this host
            $existingRows = $this->apiService->getMktRows($router, $menu, ['host' => $radiusServer]);

            if (! empty($existingRows)) {
                $this->apiService->removeMktRows($router, $menu, $existingRows);
                Log::info('Removed existing netwatch entries', [
                    'router_id' => $router->id,
                    'count' => count($existingRows),
                ]);
            }

            // Add new netwatch configuration
            $result = $this->apiService->addMktRows($router, $menu, [$netwatchRow]);

            return [
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Netwatch configured' : 'Failed to configure netwatch',
                'errors' => $result['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring netwatch for RADIUS', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove netwatch configuration.
     */
    private function removeNetwatch(MikrotikRouter $router): JsonResponse
    {
        try {
            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device',
                ], 400);
            }

            $radiusServer = config('radius.server_ip', '127.0.0.1');
            $menu = 'tool/netwatch';

            $existingRows = $this->apiService->getMktRows($router, $menu, ['host' => $radiusServer]);

            if (empty($existingRows)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No netwatch configuration found',
                ]);
            }

            $this->apiService->removeMktRows($router, $menu, $existingRows);

            Log::info('Netwatch removed successfully', [
                'router_id' => $router->id,
                'count' => count($existingRows),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Netwatch configuration removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing netwatch', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing netwatch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get netwatch status for a router.
     */
    private function getNetwatchStatus(MikrotikRouter $router): array
    {
        try {
            if (! $router->nas) {
                return [
                    'configured' => false,
                    'message' => 'Router is not associated with a NAS device',
                ];
            }

            // Use RADIUS server IP from config
            $radiusServer = config('radius.server_ip', '127.0.0.1');
            $menu = 'tool/netwatch';

            $netwatchEntries = $this->apiService->getMktRows($router, $menu);

            // Find the RADIUS netwatch entry
            $radiusNetwatch = collect($netwatchEntries)->first(function ($entry) use ($radiusServer) {
                return isset($entry['host']) && $entry['host'] === $radiusServer;
            });

            if (! $radiusNetwatch) {
                return [
                    'configured' => false,
                    'message' => 'No netwatch configuration found for RADIUS server',
                    'radius_server' => $radiusServer,
                ];
            }

            return [
                'configured' => true,
                'host' => $radiusNetwatch['host'] ?? null,
                'status' => $radiusNetwatch['status'] ?? 'unknown',
                'since' => $radiusNetwatch['since'] ?? null,
                'interval' => $radiusNetwatch['interval'] ?? null,
                'timeout' => $radiusNetwatch['timeout'] ?? null,
                'up_script' => $radiusNetwatch['up-script'] ?? null,
                'down_script' => $radiusNetwatch['down-script'] ?? null,
                'comment' => $radiusNetwatch['comment'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting netwatch status', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'configured' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
