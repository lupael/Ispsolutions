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
use Illuminate\View\View;

/**
 * NAS Netwatch Controller
 *
 * Implements RADIUS health monitoring and fallback automation
 * as described in issue #180 - Mikrotik and OLT modules re-engineering.
 *
 * This controller manages the netwatch configuration on Mikrotik routers
 * to ensure continuous operation even when the RADIUS server is down.
 */
class NasNetwatchController extends Controller
{
    public function __construct(
        private MikrotikApiService $mikrotikApiService
    ) {}

    /**
     * Display netwatch configuration for a router.
     */
    public function index(int $routerId): View
    {
        $router = MikrotikRouter::with('nas')->findOrFail($routerId);

        // Get current netwatch configuration
        $netwatchConfig = $this->getNetwatchStatus($router);

        return view('panels.admin.routers.netwatch', compact('router', 'netwatchConfig'));
    }

    /**
     * Configure netwatch for RADIUS health monitoring.
     *
     * Implements the logic described in issue #180:
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
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);

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
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);
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
     * Test netwatch functionality.
     */
    public function test(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device',
                ], 400);
            }

            // Get netwatch entries
            $netwatchEntries = $this->mikrotikApiService->getMktRows($router, '/tool/netwatch');

            // Find the RADIUS netwatch entry
            $radiusNetwatch = collect($netwatchEntries)->first(function ($entry) use ($router) {
                return isset($entry['host']) && $entry['host'] === $router->nas->server;
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
     * Configure netwatch for RADIUS health monitoring.
     */
    private function configureNetwatchForRadius(MikrotikRouter $router, array $config): array
    {
        $radiusServer = $router->nas->server;
        $interval = $config['interval'] ?? '1m';
        $timeout = $config['timeout'] ?? '1s';

        // Scripts as defined in issue #180
        $upScript = "/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];";
        $downScript = "/ppp secret enable [find disabled=yes];";

        $netwatchRow = [
            'host' => $radiusServer,
            'interval' => $interval,
            'timeout' => $timeout,
            'up-script' => $upScript,
            'down-script' => $downScript,
            'comment' => 'radius',
        ];

        try {
            // Remove any existing netwatch for this host
            $existingRows = $this->mikrotikApiService->getMktRows($router, '/tool/netwatch', ['host' => $radiusServer]);

            if (! empty($existingRows)) {
                $this->mikrotikApiService->removeMktRows($router, '/tool/netwatch', $existingRows);
                Log::info('Removed existing netwatch entries', [
                    'router_id' => $router->id,
                    'count' => count($existingRows),
                ]);
            }

            // Add new netwatch configuration
            $result = $this->mikrotikApiService->addMktRows($router, '/tool/netwatch', [$netwatchRow]);

            return [
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Netwatch configured' : 'Failed to configure netwatch',
                'details' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring netwatch for RADIUS', [
                'router_id' => $router->id,
                'radius_server' => $radiusServer,
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

            $radiusServer = $router->nas->server;
            $existingRows = $this->mikrotikApiService->getMktRows($router, '/tool/netwatch', ['host' => $radiusServer]);

            if (empty($existingRows)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No netwatch configuration found',
                ]);
            }

            $removed = $this->mikrotikApiService->removeMktRows($router, '/tool/netwatch', $existingRows);

            if ($removed) {
                Log::info('Netwatch removed successfully', [
                    'router_id' => $router->id,
                    'count' => count($existingRows),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Netwatch configuration removed successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove netwatch configuration',
            ], 500);
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

            $radiusServer = $router->nas->server;
            $netwatchEntries = $this->mikrotikApiService->getMktRows($router, '/tool/netwatch');

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
