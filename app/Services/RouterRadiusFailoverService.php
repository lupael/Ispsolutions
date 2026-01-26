<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Support\Facades\Log;

class RouterRadiusFailoverService
{
    /**
     * Configure Netwatch for RADIUS failover monitoring
     * 
     * Note: This is a placeholder implementation. The current MikrotikService implementation
     * is HTTP-based and does not expose a RouterOS API client with a comm() method.
     * This method requires future implementation when RouterOS API support is added.
     */
    public function configureFailover(MikrotikRouter $router): bool
    {
        // Check if Netwatch failover is enabled in config
        if (!config('radius.netwatch.enabled', true)) {
            Log::info('Netwatch failover is disabled in configuration', [
                'router_id' => $router->id,
            ]);
            return false;
        }

        Log::warning('Failover configuration is not fully implemented for the current MikrotikService', [
            'router_id' => $router->id,
        ]);

        return false;
    }

    /**
     * Switch router to RADIUS authentication mode
     */
    public function switchToRadiusMode(MikrotikRouter $router): bool
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return false;
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return false;
            }

            // Enable RADIUS in PPP AAA
            $api->comm('/ppp/aaa/set', [
                'use-radius' => 'yes',
                'accounting' => 'yes',
            ]);

            // Update router's primary_auth mode
            $router->update(['primary_auth' => 'radius']);

            Log::info('Switched to RADIUS mode', [
                'router_id' => $router->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to switch to RADIUS mode', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Switch router to local authentication mode (router-based)
     */
    public function switchToRouterMode(MikrotikRouter $router): bool
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return false;
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return false;
            }

            // Disable RADIUS in PPP AAA
            $api->comm('/ppp/aaa/set', [
                'use-radius' => 'no',
            ]);

            // Update router's primary_auth mode
            $router->update(['primary_auth' => 'router']);

            Log::info('Switched to router mode', [
                'router_id' => $router->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to switch to router mode', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get RADIUS and failover status
     */
    public function getRadiusStatus(MikrotikRouter $router): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'connected' => false,
                    'error' => 'Failed to connect to router',
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'connected' => false,
                    'error' => 'Router connection not available',
                ];
            }

            // Get RADIUS configuration
            $radius = $api->comm('/radius/print');
            
            // Get AAA settings
            $aaa = $api->comm('/ppp/aaa/print');
            
            // Get Netwatch status
            $nas = $router->nas;
            $radiusServer = $nas?->server ?? config('radius.server_ip', '127.0.0.1');
            
            $netwatch = $api->comm('/tool/netwatch/print', [
                '?host' => $radiusServer,
            ]);

            $netwatchConfigured = !empty($netwatch);
            $netwatchStatus = null;
            
            if ($netwatchConfigured && isset($netwatch[0]['status'])) {
                $netwatchStatus = $netwatch[0]['status'];
            }

            return [
                'connected' => true,
                'radius_configured' => !empty($radius),
                'radius_enabled' => isset($aaa[0]['use-radius']) && $aaa[0]['use-radius'] === 'yes',
                'accounting_enabled' => isset($aaa[0]['accounting']) && $aaa[0]['accounting'] === 'yes',
                'primary_auth' => $router->primary_auth,
                'netwatch_configured' => $netwatchConfigured,
                'netwatch_status' => $netwatchStatus,
                'radius_server' => $radiusServer,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get RADIUS status', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test RADIUS connectivity
     */
    public function testRadiusConnection(MikrotikRouter $router): bool
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return false;
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return false;
            }

            $nas = $router->nas;
            $radiusServer = $nas?->server ?? config('radius.server_ip', '127.0.0.1');

            // Use ping to test connectivity
            $ping = $api->comm('/ping', [
                'address' => $radiusServer,
                'count' => '3',
            ]);

            // Check if we got successful pings
            if (isset($ping[0]) && isset($ping[0]['received'])) {
                return (int) $ping[0]['received'] > 0;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('RADIUS connection test failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate script to run when RADIUS server comes back up
     */
    protected function generateUpScript(): string
    {
        return <<<'SCRIPT'
# RADIUS server is UP - switch to RADIUS mode
/ppp/aaa/set use-radius=yes accounting=yes
:log info "ISP Solution: RADIUS server is UP, switched to RADIUS authentication"
SCRIPT;
    }

    /**
     * Generate script to run when RADIUS server goes down
     */
    protected function generateDownScript(): string
    {
        return <<<'SCRIPT'
# RADIUS server is DOWN - switch to local authentication
/ppp/aaa/set use-radius=no
:log warning "ISP Solution: RADIUS server is DOWN, switched to local authentication"
SCRIPT;
    }

    /**
     * Get failover event log from router
     */
    public function getFailoverLog(MikrotikRouter $router, int $limit = 10): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [];
            }

            // Get system logs related to RADIUS failover
            $logs = $api->comm('/log/print', [
                '?message' => 'ISP Solution',
            ]);

            // Return only the most recent logs
            return array_slice($logs, 0, $limit);
        } catch (\Exception $e) {
            Log::error('Failed to get failover log', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
