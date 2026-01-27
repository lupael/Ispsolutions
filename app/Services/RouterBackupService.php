<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RouterBackupService
{
    protected MikrotikApiService $mikrotikApiService;

    public function __construct(MikrotikApiService $mikrotikApiService)
    {
        $this->mikrotikApiService = $mikrotikApiService;
    }

    /**
     * Create a pre-change backup before making configuration changes
     */
    public function createPreChangeBackup(MikrotikRouter $router, string $reason, ?int $userId = null): ?RouterConfigurationBackup
    {
        try {
            $backupData = $this->fetchRouterConfiguration($router);

            if (! $backupData) {
                return null;
            }

            return RouterConfigurationBackup::create([
                'router_id' => $router->id,
                'created_by' => $userId,
                'backup_type' => 'pre_change',
                'notes' => 'Pre-change backup: ' . $reason,
                'backup_data' => $this->encryptBackupData($backupData),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Pre-change backup failed', [
                'router_id' => $router->id,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a manual backup
     */
    public function createManualBackup(MikrotikRouter $router, string $name, ?string $reason = null, ?int $userId = null, string $backupType = 'manual'): ?RouterConfigurationBackup
    {
        try {
            $backupData = $this->fetchRouterConfiguration($router);

            if (! $backupData) {
                return null;
            }

            $notes = $name;
            if ($reason) {
                $notes .= ' - ' . $reason;
            }

            return RouterConfigurationBackup::create([
                'router_id' => $router->id,
                'created_by' => $userId,
                'backup_type' => $backupType,
                'notes' => $notes,
                'backup_data' => $this->encryptBackupData($backupData),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Manual backup failed', [
                'router_id' => $router->id,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a scheduled backup
     */
    public function createScheduledBackup(MikrotikRouter $router): ?RouterConfigurationBackup
    {
        return $this->createManualBackup(
            $router,
            'Scheduled backup - ' . now()->format('Y-m-d H:i:s'),
            'Automated scheduled backup',
            null,
            'scheduled'
        );
    }

    /**
     * Backup PPP secrets from router
     *
     * Note: This is a placeholder implementation. The current MikrotikService implementation
     * is HTTP-based and does not expose a RouterOS API client with a comm() method.
     * This method requires future implementation when RouterOS API support is added.
     */
    public function backupPppSecrets(MikrotikRouter $router): ?string
    {
        Log::warning('PPP secrets backup is not implemented for the current MikrotikService', [
            'router_id' => $router->id,
        ]);

        return null;
    }

    /**
     * Mirror customers from database to router (sync all users)
     *
     * Note: This is a placeholder implementation. The current MikrotikService implementation
     * is HTTP-based and does not expose a RouterOS API client with a comm() method.
     * This method requires future implementation when RouterOS API support is added.
     */
    public function mirrorCustomersToRouter(MikrotikRouter $router): array
    {
        Log::warning('Mirror customers to router is not fully implemented for the current MikrotikService', [
            'router_id' => $router->id,
        ]);

        return [
            'success' => false,
            'error' => 'Mirror functionality requires RouterOS API implementation',
            'synced' => 0,
            'failed' => 0,
            'total' => 0,
        ];
    }

    /**
     * Restore configuration from backup
     *
     * Parses backup data and applies configurations via MikroTik API.
     * Configurations are applied in a specific order to maintain dependencies.
     */
    public function restoreFromBackup(MikrotikRouter $router, RouterConfigurationBackup $backup): bool
    {
        try {
            if ($backup->router_id !== $router->id) {
                throw new \Exception('Backup does not belong to this router');
            }

            // Decrypt and parse backup data
            $backupDataJson = $this->decryptBackupData($backup->backup_data);
            $backupData = json_decode($backupDataJson, true);

            if (! $backupData) {
                throw new \Exception('Invalid backup data format');
            }

            Log::info('Starting restore from backup', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'backup_type' => $backup->backup_type,
            ]);

            // Create a pre-restore backup as safety measure
            $preRestoreBackup = $this->createPreChangeBackup(
                $router,
                "Pre-restore backup before restoring backup #{$backup->id}"
            );

            if (! $preRestoreBackup) {
                Log::warning('Could not create pre-restore backup, continuing anyway');
            }

            // Track restoration progress
            $restored = [];
            $failed = [];

            // Restore configurations in order of dependencies
            $sections = [
                'ip_pools' => '/ip/pool',
                'ppp_profiles' => '/ppp/profile',
                'radius_settings' => '/radius',
                'ppp_secrets' => '/ppp/secret',
                'firewall_rules' => '/firewall/filter',
            ];

            foreach ($sections as $section => $menu) {
                if (isset($backupData[$section]) && is_array($backupData[$section])) {
                    try {
                        $items = $backupData[$section];

                        // Skip if no items to restore
                        if (empty($items)) {
                            continue;
                        }

                        // Restore items to router
                        $success = $this->mikrotikApiService->addMktRows($router, $menu, $items);

                        if ($success) {
                            $restored[$section] = count($items);
                            Log::info("Restored {$section}", [
                                'router_id' => $router->id,
                                'count' => count($items),
                            ]);
                        } else {
                            $failed[$section] = count($items);
                            Log::warning("Failed to restore {$section}", [
                                'router_id' => $router->id,
                                'count' => count($items),
                            ]);
                        }
                    } catch (\Exception $e) {
                        $failed[$section] = $e->getMessage();
                        Log::error("Error restoring {$section}", [
                            'router_id' => $router->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Log overall results
            $totalRestored = array_sum($restored);
            $hasFailures = ! empty($failed);

            Log::info('Restore from backup completed', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'restored_sections' => $restored,
                'failed_sections' => $failed,
                'total_restored' => $totalRestored,
            ]);

            // Return true if at least some sections were restored and no critical failures
            return $totalRestored > 0 && ! $hasFailures;

        } catch (\Exception $e) {
            Log::error('Restore from backup failed', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * List all backups for a router
     */
    public function listBackups(MikrotikRouter $router, ?string $type = null): Collection
    {
        $query = RouterConfigurationBackup::where('router_id', $router->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('backup_type', $type);
        }

        return $query->get();
    }

    /**
     * Delete old backups based on retention policy
     */
    public function cleanupOldBackups(MikrotikRouter $router, int $retentionDays = 30): int
    {
        $cutoffDate = now()->subDays($retentionDays);

        return RouterConfigurationBackup::where('router_id', $router->id)
            ->where('backup_type', 'scheduled')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Fetch router configuration via API
     */
    protected function fetchRouterConfiguration(MikrotikRouter $router): ?string
    {
        try {
            $configuration = [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'timestamp' => now()->toDateTimeString(),
            ];

            // Fetch various configuration sections
            $sections = [
                'ip_pools' => '/ip/pool',
                'ppp_profiles' => '/ppp/profile',
                'radius_settings' => '/radius',
                'ppp_secrets' => '/ppp/secret',
                'firewall_rules' => '/firewall/filter',
            ];

            foreach ($sections as $section => $menu) {
                try {
                    $data = $this->mikrotikApiService->getMktRows($router, $menu);
                    $configuration[$section] = $data;
                } catch (\Exception $e) {
                    Log::warning("Failed to fetch {$section} during backup", [
                        'router_id' => $router->id,
                        'section' => $section,
                        'error' => $e->getMessage(),
                    ]);
                    $configuration[$section] = [];
                }
            }

            return json_encode($configuration, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to fetch router configuration', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Encrypt backup data to protect sensitive information like passwords and secrets
     */
    protected function encryptBackupData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt backup data
     */
    protected function decryptBackupData(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }
}
