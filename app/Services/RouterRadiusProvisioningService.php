<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\Nas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Router RADIUS Provisioning Service
 *
 * Handles automated provisioning of RADIUS configuration on routers.
 * Implements the NAS-centric approach with automatic configuration on first connect.
 */
class RouterRadiusProvisioningService
{
    public function __construct(
        private readonly MikrotikApiService $apiService
    ) {}

    /**
     * Perform automated provisioning on router first connect.
     *
     * Steps:
     * 1. Configure RADIUS client
     * 2. Configure PPP AAA
     * 3. Create initial backup
     * 4. Auto-insert router into RADIUS nas table
     *
     * @return array Result array with success status and details
     */
    public function provisionOnFirstConnect(MikrotikRouter $router): array
    {
        DB::beginTransaction();

        try {
            $nas = $router->nas;

            if (! $nas) {
                throw new \RuntimeException('Router must be associated with a NAS device');
            }

            $results = [
                'radius_client' => false,
                'ppp_aaa' => false,
                'backup' => false,
                'nas_table' => false,
            ];

            // Step 1: Configure RADIUS client
            $results['radius_client'] = $this->configureRadiusClient($router, $nas);

            // Step 2: Configure PPP AAA
            $results['ppp_aaa'] = $this->configurePppAaa($router);

            // Step 3: Configure RADIUS incoming
            $results['radius_incoming'] = $this->configureRadiusIncoming($router);

            // Step 4: Create initial backup
            $results['backup'] = $this->createInitialBackup($router);

            // Step 5: Ensure router is in RADIUS nas table
            $results['nas_table'] = $this->ensureNasTableEntry($router, $nas);

            $allSuccess = $results['radius_client']
                && $results['ppp_aaa']
                && $results['radius_incoming']
                && $results['nas_table'];

            if ($allSuccess) {
                DB::commit();

                Log::info('Successfully provisioned router for RADIUS', [
                    'router_id' => $router->id,
                    'router_name' => $router->name,
                ]);

                return [
                    'success' => true,
                    'message' => 'Router provisioned successfully for RADIUS authentication',
                    'steps' => $results,
                ];
            }

            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Router provisioning completed with some failures',
                'steps' => $results,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error provisioning router for RADIUS', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'steps' => $results ?? [],
            ];
        }
    }

    /**
     * Configure RADIUS client on router.
     * Executes: /radius add service=ppp,hotspot address=[Server_IP]
     */
    private function configureRadiusClient(MikrotikRouter $router, Nas $nas): bool
    {
        try {
            $menu = 'radius';
            $radiusServer = $nas->server;
            $secret = $nas->secret;

            // Check if RADIUS client already exists
            $existingRows = $this->apiService->getMktRows($router, $menu, ['address' => $radiusServer]);

            if (! empty($existingRows)) {
                // Update existing RADIUS client
                $existRow = array_shift($existingRows);
                $radiusConfig = [
                    'accounting-port' => config('radius.accounting_port', 1813),
                    'authentication-port' => config('radius.authentication_port', 1812),
                    'secret' => $secret,
                    'service' => 'hotspot,ppp',
                    'timeout' => config('radius.timeout', '3s'),
                ];

                return $this->apiService->editMktRow($router, $menu, $existRow, $radiusConfig);
            }

            // Add new RADIUS client
            $rows = [[
                'accounting-port' => config('radius.accounting_port', 1813),
                'address' => $radiusServer,
                'authentication-port' => config('radius.authentication_port', 1812),
                'secret' => $secret,
                'service' => 'hotspot,ppp',
                'timeout' => config('radius.timeout', '3s'),
                'comment' => 'Auto-configured by ISP Solution',
            ]];

            $result = $this->apiService->addMktRows($router, $menu, $rows);

            return $result['success'];

        } catch (\Exception $e) {
            Log::error('Error configuring RADIUS client', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure PPP AAA on router.
     * Executes: /ppp aaa set use-radius=yes accounting=yes interim-update=5m
     */
    private function configurePppAaa(MikrotikRouter $router): bool
    {
        try {
            // Use ttyWrite for AAA configuration
            $result = $this->apiService->ttyWrite($router, '/ppp/aaa/set', [
                'interim-update' => config('radius.interim_update', '5m'),
                'use-radius' => 'yes',
                'accounting' => 'yes',
            ]);

            return $result !== null;

        } catch (\Exception $e) {
            Log::error('Error configuring PPP AAA', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure RADIUS incoming on router.
     * Executes: /radius incoming set accept=yes
     */
    private function configureRadiusIncoming(MikrotikRouter $router): bool
    {
        try {
            $result = $this->apiService->ttyWrite($router, '/radius/incoming/set', [
                'accept' => 'yes',
            ]);

            return $result !== null;

        } catch (\Exception $e) {
            Log::error('Error configuring RADIUS incoming', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create initial backup of router configuration.
     * Executes: /system backup save
     */
    private function createInitialBackup(MikrotikRouter $router): bool
    {
        try {
            $timestamp = now()->timestamp;
            $filename = "initial-backup-{$timestamp}";

            $result = $this->apiService->ttyWrite($router, '/system/backup/save', [
                'name' => $filename,
                'dont-encrypt' => 'yes',
            ]);

            if ($result !== null) {
                Log::info('Created initial backup on router', [
                    'router_id' => $router->id,
                    'filename' => $filename,
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error creating initial backup', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Ensure router is present in RADIUS nas table.
     * Auto-inserts if not present.
     */
    private function ensureNasTableEntry(MikrotikRouter $router, Nas $nas): bool
    {
        try {
            // Check if NAS entry exists in database
            // The Nas model already has the router relationship,
            // so we just need to ensure the router has a nas_id

            if ($router->nas_id) {
                Log::info('Router already associated with NAS', [
                    'router_id' => $router->id,
                    'nas_id' => $router->nas_id,
                ]);

                return true;
            }

            // Associate router with NAS
            $router->update(['nas_id' => $nas->id]);

            Log::info('Associated router with NAS', [
                'router_id' => $router->id,
                'nas_id' => $nas->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error ensuring NAS table entry', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Export PPP secrets from router during import.
     * Creates a backup file on the router with current secrets.
     *
     * @return array Result with success status and filename
     */
    public function exportPppSecrets(MikrotikRouter $router): array
    {
        try {
            $timestamp = now()->timestamp;
            $filename = "ppp-secret-backup-by-billing-{$timestamp}";

            $result = $this->apiService->ttyWrite($router, '/ppp/secret/export', [
                'file' => $filename,
            ]);

            if ($result !== null) {
                Log::info('Exported PPP secrets from router', [
                    'router_id' => $router->id,
                    'filename' => $filename,
                ]);

                return [
                    'success' => true,
                    'filename' => $filename,
                    'message' => 'PPP secrets exported successfully',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to export PPP secrets',
            ];

        } catch (\Exception $e) {
            Log::error('Error exporting PPP secrets', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
