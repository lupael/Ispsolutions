<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Services\RouterosAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * RouterConfigurationController - Configure MikroTik routers for RADIUS
 * 
 * Following IspBills pattern for router configuration
 */
class RouterConfigurationController extends Controller
{
    /**
     * Display router configuration interface.
     */
    public function index(): View
    {
        $routers = MikrotikRouter::with('nas')->where('tenant_id', getCurrentTenantId())->paginate(20);
        
        return view('panels.admin.network.router-configure', compact('routers'));
    }

    /**
     * Show router configuration details.
     */
    public function show(int $routerId): View
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);
        
        return view('panels.admin.network.router-dashboard', compact('router'));
    }

    /**
     * Configure RADIUS on router (IspBills pattern).
     * 
     * This performs complete one-click RADIUS setup:
     * 1. Add RADIUS client configuration
     * 2. Enable PPP AAA to use RADIUS
     * 3. Enable RADIUS incoming
     */
    public function configureRadius(int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        if (!$router->nas) {
            return response()->json([
                'success' => false,
                'error' => 'Router is not associated with a NAS device',
            ], 400);
        }

        // Connect to router using RouterosAPI
        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router. Check credentials and network connectivity.',
            ], 500);
        }

        try {
            // Step 1: Configure RADIUS client on router
            $radiusServer = config('radius.server_ip', '127.0.0.1');
            
            // Remove existing RADIUS configuration for this server
            $existingRows = $api->getMktRows('radius', ['address' => $radiusServer]);
            if (!empty($existingRows)) {
                $removeResult = $api->removeMktRows('radius', $existingRows);
                if (!$removeResult) {
                    throw new \RuntimeException('Failed to remove existing RADIUS configuration');
                }
            }
            
            // Add new RADIUS client configuration
            $radiusConfig = [[
                'accounting-port' => config('radius.accounting_port', 1813),
                'address' => $radiusServer,
                'authentication-port' => config('radius.authentication_port', 1812),
                'secret' => $router->radius_secret ?? $router->nas->secret,
                'service' => 'hotspot,ppp',
                'timeout' => '3s',
                'require-message-auth' => 'no',
            ]];
            
            $addResult = $api->addMktRows('radius', $radiusConfig);
            if (!$addResult) {
                throw new \RuntimeException('Failed to add RADIUS client configuration');
            }
            
            // Step 2: Enable PPP AAA to use RADIUS + accounting
            $pppResult = $api->ttyWrite('/ppp/aaa/set', [
                'interim-update' => config('radius.interim_update', '5m'),
                'use-radius' => 'yes',
                'accounting' => 'yes',
            ]);
            
            if ($pppResult === null) {
                throw new \RuntimeException('Failed to configure PPP AAA on router');
            }
            
            // Step 3: Enable RADIUS incoming
            $radiusIncomingResult = $api->ttyWrite('/radius/incoming/set', [
                'accept' => 'yes',
            ]);
            
            if ($radiusIncomingResult === null) {
                throw new \RuntimeException('Failed to configure RADIUS incoming on router');
            }
            
            $api->disconnect();
            
            Log::info('Router RADIUS configuration completed', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'RADIUS configuration applied successfully',
            ]);
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to configure RADIUS on router', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to configure RADIUS: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configure PPP profiles on router (IspBills pattern).
     * 
     * Updates all PPP profiles to set local-address for proper PPPoE operation.
     */
    public function configurePpp(int $routerId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router',
            ], 500);
        }

        try {
            // Get local address from config or use default
            $localAddress = config('mikrotik.ppp_local_address', '10.0.0.1');
            
            // Get all default PPP profiles
            $pppProfiles = $api->getMktRows('ppp_profile', ['default' => 'yes']);
            
            $updatedCount = 0;
            foreach ($pppProfiles as $profile) {
                $api->editMktRow('ppp_profile', $profile, ['local-address' => $localAddress]);
                $updatedCount++;
            }
            
            $api->disconnect();
            
            Log::info('PPP profiles updated', [
                'router_id' => $router->id,
                'updated_count' => $updatedCount,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} PPP profiles successfully",
            ]);
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to configure PPP profiles', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to configure PPP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configure firewall on router.
     */
    public function configureFirewall(int $routerId): JsonResponse
    {
        // Placeholder - firewall configuration is router-specific
        // and typically done manually or through templates
        return response()->json([
            'success' => false,
            'error' => 'Firewall configuration should be done manually or through configuration templates',
        ], 501);
    }

    /**
     * Get RADIUS configuration status.
     */
    public function radiusStatus(int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'debug' => false,
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router',
                'status' => [
                    'radius_configured' => false,
                    'ppp_aaa_enabled' => false,
                    'radius_incoming_enabled' => false,
                ],
            ], 500);
        }

        try {
            // Check RADIUS client configuration
            $radiusRows = $api->getMktRows('radius');
            $radiusConfigured = !empty($radiusRows);
            
            // Check PPP AAA settings
            $pppAaaSettings = $api->ttyWrite('/ppp/aaa/print');
            $pppAaaEnabled = false;
            if (is_array($pppAaaSettings) && !empty($pppAaaSettings)) {
                $firstSetting = $pppAaaSettings[0] ?? [];
                $pppAaaEnabled = ($firstSetting['use-radius'] ?? 'no') === 'yes';
            }
            
            // Check RADIUS incoming
            $radiusIncoming = $api->ttyWrite('/radius/incoming/print');
            $radiusIncomingEnabled = false;
            if (is_array($radiusIncoming) && !empty($radiusIncoming)) {
                $firstIncoming = $radiusIncoming[0] ?? [];
                $radiusIncomingEnabled = ($firstIncoming['accept'] ?? 'no') === 'yes';
            }
            
            $api->disconnect();
            
            return response()->json([
                'success' => true,
                'status' => [
                    'radius_configured' => $radiusConfigured,
                    'radius_count' => count($radiusRows),
                    'ppp_aaa_enabled' => $pppAaaEnabled,
                    'radius_incoming_enabled' => $radiusIncomingEnabled,
                    'fully_configured' => $radiusConfigured && $pppAaaEnabled && $radiusIncomingEnabled,
                ],
            ]);
        } catch (\Exception $e) {
            $api->disconnect();
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'status' => [
                    'radius_configured' => false,
                    'ppp_aaa_enabled' => false,
                    'radius_incoming_enabled' => false,
                ],
            ], 500);
        }
    }
}
