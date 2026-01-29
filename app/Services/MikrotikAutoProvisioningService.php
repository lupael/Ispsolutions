<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\Nas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mikrotik Auto-Provisioning Service
 *
 * Implements automated provisioning on first connect as described in issue #180:
 * - RADIUS Client: Auto-runs /radius add service=ppp,hotspot address=[Server_IP]
 * - PPP AAA: Auto-sets /ppp aaa set use-radius=yes
 * - Backup: Triggers initial /system backup save
 * - NAS Table: Auto-inserts the router into the RADIUS nas database table
 */
class MikrotikAutoProvisioningService
{
    public function __construct(
        private MikrotikApiService $mikrotikApiService
    ) {}

    /**
     * Execute automated provisioning on first connect.
     *
     * This method is called when a router connects for the first time or
     * when auto-provisioning is explicitly triggered.
     *
     * @return array{success: bool, message: string, steps: array<string, array{success: bool, message: string}>}
     */
    public function provisionOnFirstConnect(MikrotikRouter $router, ?string $radiusServerIp = null): array
    {
        $steps = [];
        $radiusServer = $radiusServerIp ?? config('radius.server_ip', '127.0.0.1');
        $allSuccessful = true;

        try {
            DB::beginTransaction();

            // Step 1: Ensure NAS entry exists
            $steps['nas_entry'] = $this->ensureNasEntry($router, $radiusServer);

            if (! $steps['nas_entry']['success']) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Failed to create/update NAS entry',
                    'steps' => $steps,
                ];
            }

            // Step 2: Configure RADIUS client on router
            $steps['radius_client'] = $this->configureRadiusClient($router, $radiusServer);
            if (! $steps['radius_client']['success']) {
                $allSuccessful = false;
            }

            // Step 3: Configure PPP AAA to use RADIUS
            $steps['ppp_aaa'] = $this->configurePppAaa($router);
            if (! $steps['ppp_aaa']['success']) {
                $allSuccessful = false;
            }

            // Step 4: Configure RADIUS incoming
            $steps['radius_incoming'] = $this->configureRadiusIncoming($router);
            if (! $steps['radius_incoming']['success']) {
                $allSuccessful = false;
            }

            // Step 5: Create initial backup
            $steps['initial_backup'] = $this->createInitialBackup($router);
            // Backup failure is not critical, continue

            // Step 6: Configure netwatch for RADIUS health monitoring
            $steps['netwatch'] = $this->configureNetwatch($router, $radiusServer);
            if (! $steps['netwatch']['success']) {
                $allSuccessful = false;
            }

            // If critical steps failed, rollback
            if (! $allSuccessful) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Auto-provisioning completed with failures',
                    'steps' => $steps,
                ];
            }

            // Mark router as provisioned
            $router->update([
                'api_status' => 'provisioned',
                'last_checked_at' => now(),
            ]);

            DB::commit();

            Log::info('Auto-provisioning completed successfully', [
                'router_id' => $router->id,
                'steps' => array_map(fn ($step) => $step['success'], $steps),
            ]);

            return [
                'success' => true,
                'message' => 'Auto-provisioning completed successfully',
                'steps' => $steps,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Auto-provisioning failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Auto-provisioning failed: '.$e->getMessage(),
                'steps' => $steps,
            ];
        }
    }

    /**
     * Ensure NAS table entry exists for the router.
     */
    private function ensureNasEntry(MikrotikRouter $router, string $radiusServer): array
    {
        try {
            // Check if router already has a NAS entry
            if ($router->nas_id) {
                $nas = $router->nas;

                if ($nas) {
                    Log::info('NAS entry already exists', [
                        'router_id' => $router->id,
                        'nas_id' => $nas->id,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'NAS entry already exists',
                        'nas_id' => $nas->id,
                    ];
                }
            }

            // Create new NAS entry
            $secret = $router->radius_secret ?? bin2hex(random_bytes(16));

            $nas = Nas::create([
                'tenant_id' => $router->tenant_id,
                'name' => $router->name.' NAS',
                'nas_name' => $router->name,
                'short_name' => substr($router->name, 0, 32),
                'server' => $router->ip_address,
                'secret' => $secret,
                'type' => 'mikrotik',
                'ports' => 1812,
                'community' => '',
                'description' => 'Auto-created NAS entry for '.$router->name,
                'status' => 'active',
            ]);

            // Update router to link to NAS
            $router->update([
                'nas_id' => $nas->id,
                'radius_secret' => $secret,
            ]);

            Log::info('Created new NAS entry', [
                'router_id' => $router->id,
                'nas_id' => $nas->id,
            ]);

            return [
                'success' => true,
                'message' => 'NAS entry created successfully',
                'nas_id' => $nas->id,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create NAS entry', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create NAS entry: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Configure RADIUS client on the router.
     *
     * Implements: /radius add service=ppp,hotspot address=[Server_IP]
     */
    private function configureRadiusClient(MikrotikRouter $router, string $radiusServer): array
    {
        try {
            $nas = $router->nas;

            if (! $nas) {
                return [
                    'success' => false,
                    'message' => 'NAS entry not found',
                ];
            }

            // Check if RADIUS client already exists
            $existingRadius = $this->mikrotikApiService->getMktRows($router, '/radius', ['address' => $radiusServer]);

            if (! empty($existingRadius)) {
                Log::info('RADIUS client already configured', [
                    'router_id' => $router->id,
                    'radius_server' => $radiusServer,
                ]);

                return [
                    'success' => true,
                    'message' => 'RADIUS client already configured',
                ];
            }

            // Add RADIUS client
            $radiusConfig = [
                'accounting-port' => '1813',
                'address' => $radiusServer,
                'authentication-port' => '1812',
                'secret' => $nas->secret,
                'service' => 'hotspot,ppp',
                'timeout' => '3s',
                'require-message-auth' => 'no',
            ];

            $result = $this->mikrotikApiService->addMktRows($router, '/radius', [$radiusConfig]);

            if ($result['success']) {
                Log::info('RADIUS client configured successfully', [
                    'router_id' => $router->id,
                    'radius_server' => $radiusServer,
                ]);

                return [
                    'success' => true,
                    'message' => 'RADIUS client configured successfully',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to configure RADIUS client',
                'errors' => $result['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring RADIUS client', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Configure PPP AAA to use RADIUS.
     *
     * Implements: /ppp aaa set use-radius=yes accounting=yes interim-update=5m
     */
    private function configurePppAaa(MikrotikRouter $router): array
    {
        try {
            $result = $this->mikrotikApiService->ttyWrite($router, '/ppp/aaa/set', [
                'interim-update' => '5m',
                'use-radius' => 'yes',
                'accounting' => 'yes',
            ]);

            if ($result !== null) {
                Log::info('PPP AAA configured successfully', [
                    'router_id' => $router->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'PPP AAA configured to use RADIUS',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to configure PPP AAA',
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring PPP AAA', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Configure RADIUS incoming requests.
     *
     * Implements: /radius incoming set accept=yes
     */
    private function configureRadiusIncoming(MikrotikRouter $router): array
    {
        try {
            $result = $this->mikrotikApiService->ttyWrite($router, '/radius/incoming/set', [
                'accept' => 'yes',
            ]);

            if ($result !== null) {
                Log::info('RADIUS incoming configured successfully', [
                    'router_id' => $router->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'RADIUS incoming configured',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to configure RADIUS incoming',
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring RADIUS incoming', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create initial backup of router configuration.
     *
     * Implements: /system backup save
     */
    private function createInitialBackup(MikrotikRouter $router): array
    {
        try {
            $timestamp = Carbon::now()->timestamp;
            $filename = "initial-backup-by-billing-{$timestamp}";

            // Export PPP secrets first
            $secretExportResult = $this->mikrotikApiService->ttyWrite($router, '/ppp/secret/export', [
                'file' => "ppp-secret-backup-by-billing-{$timestamp}",
            ]);

            // Create system backup
            $backupResult = $this->mikrotikApiService->ttyWrite($router, '/system/backup/save', [
                'name' => $filename,
            ]);

            if ($backupResult !== null) {
                Log::info('Initial backup created successfully', [
                    'router_id' => $router->id,
                    'filename' => $filename,
                ]);

                return [
                    'success' => true,
                    'message' => 'Initial backup created',
                    'filename' => $filename,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create initial backup',
            ];
        } catch (\Exception $e) {
            Log::error('Error creating initial backup', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Configure netwatch for RADIUS health monitoring.
     *
     * Implements the logic described in issue #180:
     * - RADIUS UP: Force RADIUS authentication (disable local secrets, drop non-radius sessions)
     * - RADIUS DOWN: Enable local secrets as fallback
     *
     * This ensures users can still authenticate locally when RADIUS is down.
     */
    private function configureNetwatch(MikrotikRouter $router, string $radiusServer): array
    {
        try {
            // Remove any existing netwatch for this RADIUS server
            $existingNetwatch = $this->mikrotikApiService->getMktRows($router, '/tool/netwatch', ['host' => $radiusServer]);

            if (! empty($existingNetwatch)) {
                $this->mikrotikApiService->removeMktRows($router, '/tool/netwatch', $existingNetwatch);
            }

            // Add netwatch configuration
            // UP script: RADIUS is working, force all auth through RADIUS
            // DOWN script: RADIUS is down, enable local secrets as fallback
            $netwatchConfig = [
                'host' => $radiusServer,
                'interval' => '1m',
                'timeout' => '1s',
                'up-script' => '/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];',
                'down-script' => '/ppp secret enable [find disabled=yes];',
                'comment' => 'radius',
            ];

            $result = $this->mikrotikApiService->addMktRows($router, '/tool/netwatch', [$netwatchConfig]);

            if ($result['success']) {
                Log::info('Netwatch configured successfully', [
                    'router_id' => $router->id,
                    'radius_server' => $radiusServer,
                ]);

                return [
                    'success' => true,
                    'message' => 'Netwatch configured for RADIUS health monitoring',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to configure netwatch',
                'errors' => $result['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Error configuring netwatch', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if router has been provisioned.
     */
    public function isProvisioned(MikrotikRouter $router): bool
    {
        // Check if router has NAS entry
        if (! $router->nas_id) {
            return false;
        }

        // Check if router has RADIUS configuration
        try {
            $radiusConfig = $this->mikrotikApiService->getMktRows($router, '/radius');

            return ! empty($radiusConfig);
        } catch (\Exception $e) {
            Log::error('Error checking provisioning status', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
