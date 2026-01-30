<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\Nas;
use Illuminate\Support\Facades\Log;

class RouterConfigurationService
{
    /**
     * Configure RADIUS authentication on the router
     * Complete one-click setup following IspBills pattern
     */
    public function configureRadius(MikrotikRouter $router): array
    {
        // Get NAS configuration
        $nas = $router->nas;
        if (!$nas) {
            return [
                'success' => false,
                'error' => 'Router is not associated with a NAS device',
            ];
        }

        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to router',
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'success' => false,
                    'error' => 'Router connection not available',
                ];
            }

            $results = [
                'radius_client' => false,
                'ppp_aaa' => false,
                'radius_incoming' => false,
            ];

            // 1. Configure RADIUS client
            $this->configureRadiusClient($api, $router, $nas);
            $results['radius_client'] = true;

            // 2. Configure PPP AAA
            $this->configurePppAaa($api);
            $results['ppp_aaa'] = true;

            // 3. Enable RADIUS incoming
            $this->configureRadiusIncoming($api);
            $results['radius_incoming'] = true;

            Log::info('RADIUS configuration completed', [
                'router_id' => $router->id,
                'results' => $results,
            ]);

            return [
                'success' => true,
                'message' => 'RADIUS configuration completed successfully',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('RADIUS configuration failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configure RADIUS client on the router
     * 
     * Note: Placeholder - requires RouterOS API implementation
     */
    public function configureRadiusClient($api, MikrotikRouter $router, Nas $nas): void
    {
        // Remove only ISP Solution managed RADIUS entries (identified by comment)
        // This avoids clobbering RADIUS config for other services
        $existing = $api->comm('/radius/print', [
            '?comment' => 'ISP Solution RADIUS Server',
        ]);
        
        foreach ($existing as $item) {
            $api->comm('/radius/remove', [
                '.id' => $item['.id'],
            ]);
        }

        // Add new RADIUS server configuration
        $api->comm('/radius/add', [
            'service' => 'ppp',
            'address' => $nas->server ?? config('radius.server_ip', '127.0.0.1'),
            'secret' => $router->radius_secret ?? $nas->secret,
            'authentication-port' => config('radius.authentication_port', 1812),
            'accounting-port' => config('radius.accounting_port', 1813),
            'timeout' => '3s',
            'comment' => 'ISP Solution RADIUS Server',
        ]);
    }

    /**
     * Configure PPP AAA to use RADIUS
     */
    public function configurePppAaa($api): void
    {
        // Enable RADIUS for PPP authentication
        $api->comm('/ppp/aaa/set', [
            'use-radius' => 'yes',
            'accounting' => 'yes',
            'interim-update' => config('radius.interim_update', '5m'),
        ]);
    }

    /**
     * Configure RADIUS Incoming settings
     */
    public function configureRadiusIncoming($api): void
    {
        // Enable RADIUS incoming requests (for dynamic client addition if needed)
        try {
            $api->comm('/radius/incoming/set', [
                'accept' => 'yes',
            ]);
        } catch (\Exception $e) {
            // Some MikroTik versions may not support this, log and continue
            Log::warning('RADIUS incoming configuration skipped', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update PPP profiles to use RADIUS authentication
     */
    public function updatePppProfiles($api, MikrotikRouter $router): void
    {
        $profiles = $api->comm('/ppp/profile/print');

        foreach ($profiles as $profile) {
            // Skip the default profile if it exists
            if (isset($profile['name']) && $profile['name'] === 'default-encryption') {
                continue;
            }

            try {
                // Update profile to use RADIUS
                $api->comm('/ppp/profile/set', [
                    '.id' => $profile['.id'],
                    'use-encryption' => 'yes',
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to update PPP profile', [
                    'profile_id' => $profile['.id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Configure PPP server settings
     */
    public function configurePpp(MikrotikRouter $router): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to router',
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'success' => false,
                    'error' => 'Router connection not available',
                ];
            }

            // Configure PPPoE server
            // Note: '0' => 'default-profile' sets the default profile for the first PPPoE server interface
            $api->comm('/interface/pppoe-server/server/set', [
                '0' => 'default-profile', // MikroTik API index for the first server interface
                'service-name' => config('app.name', 'ISP Solution'),
                'authentication' => 'pap,chap,mschap1,mschap2',
                'keepalive-timeout' => '10',
                'max-mtu' => '1480',
                'max-mru' => '1480',
                'mrru' => 'disabled',
            ]);

            Log::info('PPP configuration completed', [
                'router_id' => $router->id,
            ]);

            return [
                'success' => true,
                'message' => 'PPP server configured successfully',
            ];
        } catch (\Exception $e) {
            Log::error('PPP configuration failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configure basic firewall rules
     */
    public function configureFirewall(MikrotikRouter $router): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to router',
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'success' => false,
                    'error' => 'Router connection not available',
                ];
            }

            $rules = [
                [
                    'chain' => 'input',
                    'protocol' => 'icmp',
                    'action' => 'accept',
                    'comment' => 'Allow ICMP',
                ],
                [
                    'chain' => 'input',
                    'connection-state' => 'established,related',
                    'action' => 'accept',
                    'comment' => 'Allow established/related',
                ],
                [
                    'chain' => 'input',
                    'protocol' => 'tcp',
                    'dst-port' => config('mikrotik.port', 8728),
                    'action' => 'accept',
                    'comment' => 'Allow API access',
                ],
            ];

            foreach ($rules as $rule) {
                try {
                    $api->comm('/ip/firewall/filter/add', $rule);
                } catch (\Exception $e) {
                    Log::warning('Failed to add firewall rule', [
                        'rule' => $rule,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Firewall configuration completed', [
                'router_id' => $router->id,
            ]);

            return [
                'success' => true,
                'message' => 'Basic firewall rules configured',
            ];
        } catch (\Exception $e) {
            Log::error('Firewall configuration failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current RADIUS configuration status
     */
    public function getRadiusStatus(MikrotikRouter $router): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'configured' => false,
                    'error' => 'Failed to connect to router',
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'configured' => false,
                    'error' => 'Router connection not available',
                ];
            }

            $radius = $api->comm('/radius/print');
            $aaa = $api->comm('/ppp/aaa/print');

            return [
                'configured' => !empty($radius),
                'radius_servers' => count($radius),
                'aaa_enabled' => isset($aaa[0]['use-radius']) && $aaa[0]['use-radius'] === 'yes',
                'accounting_enabled' => isset($aaa[0]['accounting']) && $aaa[0]['accounting'] === 'yes',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get RADIUS status', [
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
