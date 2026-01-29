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
 * NAS NetWatch Controller
 *
 * Implements RADIUS health monitoring with automatic failover.
 * When RADIUS server goes down, router automatically enables local secrets.
 * When RADIUS comes back up, router switches back to RADIUS authentication.
 */
class NasNetWatchController extends Controller
{
    public function __construct(
        private readonly MikrotikApiService $apiService
    ) {}

    /**
     * Configure netwatch for RADIUS health monitoring on a router.
     */
    public function configure(Request $request, int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router must be associated with a NAS device',
                ], 400);
            }

            $radiusServer = $router->nas->server;

            if (empty($radiusServer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'NAS device does not have a server address configured',
                ], 400);
            }

            // Netwatch configuration
            $menu = 'tool/netwatch';
            $rows = [[
                'host' => $radiusServer,
                'interval' => config('radius.netwatch.interval', '1m'),
                'timeout' => config('radius.netwatch.timeout', '1s'),
                'up-script' => '/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];',
                'down-script' => '/ppp secret enable [find disabled=yes];',
                'comment' => 'radius-health-monitor',
            ]];

            // Remove existing netwatch entries for this RADIUS server
            $existingRows = $this->apiService->getMktRows($router, $menu, ['host' => $radiusServer]);

            if (! empty($existingRows)) {
                $this->apiService->removeMktRows($router, $menu, $existingRows);
                Log::info('Removed existing netwatch entries', [
                    'router_id' => $routerId,
                    'count' => count($existingRows),
                ]);
            }

            // Add new netwatch entry
            $result = $this->apiService->addMktRows($router, $menu, $rows);

            if ($result['success']) {
                Log::info('Successfully configured netwatch for RADIUS monitoring', [
                    'router_id' => $routerId,
                    'radius_server' => $radiusServer,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Netwatch configured successfully',
                    'radius_server' => $radiusServer,
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove netwatch configuration from a router.
     */
    public function remove(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device',
                ], 400);
            }

            $radiusServer = $router->nas->server;
            $menu = 'tool/netwatch';

            // Find and remove netwatch entries for this RADIUS server
            $existingRows = $this->apiService->getMktRows($router, $menu, ['host' => $radiusServer]);

            if (empty($existingRows)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No netwatch configuration found',
                ]);
            }

            $this->apiService->removeMktRows($router, $menu, $existingRows);

            Log::info('Successfully removed netwatch configuration', [
                'router_id' => $routerId,
                'radius_server' => $radiusServer,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Netwatch configuration removed successfully',
            ]);

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
     * Get netwatch status for a router.
     */
    public function status(int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::with('nas')->findOrFail($routerId);

            if (! $router->nas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not associated with a NAS device',
                ], 400);
            }

            $menu = 'tool/netwatch';
            $radiusServer = $router->nas->server;

            // Get netwatch entries
            $entries = $this->apiService->getMktRows($router, $menu, ['host' => $radiusServer]);

            return response()->json([
                'success' => true,
                'configured' => ! empty($entries),
                'entries' => $entries,
                'radius_server' => $radiusServer,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting netwatch status', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
