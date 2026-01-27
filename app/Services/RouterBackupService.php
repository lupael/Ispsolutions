<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RouterBackupService
{
    /**
     * Create a pre-change backup before making configuration changes
     */
    public function createPreChangeBackup(MikrotikRouter $router, string $reason, ?int $userId = null): ?RouterConfigurationBackup
    {
        try {
            $backupData = $this->fetchRouterConfiguration($router);
            
            if (!$backupData) {
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
            
            if (!$backupData) {
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
     * This implementation restores configurations from backup data stored in the database.
     * The backup data is expected to be in JSON format containing router configuration.
     * 
     * @param MikrotikRouter $router The router to restore configuration to
     * @param RouterConfigurationBackup $backup The backup to restore from
     * @return bool True if restore was successful, false otherwise
     * @throws \Exception If backup doesn't belong to the router
     */
    public function restoreFromBackup(MikrotikRouter $router, RouterConfigurationBackup $backup): bool
    {
        try {
            if ($backup->router_id !== $router->id) {
                throw new \Exception('Backup does not belong to this router');
            }

            Log::info('Starting restore from backup', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'backup_type' => $backup->backup_type,
                'notes' => $backup->notes,
            ]);

            // Decrypt the backup data
            if (!$backup->backup_data) {
                throw new \Exception('Backup data is empty or invalid');
            }

            $backupData = $this->decryptBackupData($backup->backup_data);
            $config = json_decode($backupData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to parse backup data: ' . json_last_error_msg());
            }

            // Validate backup structure
            if (!isset($config['router_id']) || $config['router_id'] != $router->id) {
                throw new \Exception('Backup router ID does not match target router');
            }

            // Initialize MikrotikService
            $mikrotikService = app(MikrotikService::class);
            
            // Connect to the router
            if (!$mikrotikService->connectRouter($router->id)) {
                throw new \Exception('Failed to connect to router');
            }

            $restored = [];
            $failed = [];

            // Restore PPP Profiles if present in backup
            if (isset($config['ppp_profiles']) && is_array($config['ppp_profiles'])) {
                foreach ($config['ppp_profiles'] as $profile) {
                    try {
                        if ($mikrotikService->createPppProfile($router->id, $profile)) {
                            $restored[] = 'Profile: ' . ($profile['name'] ?? 'unknown');
                        } else {
                            $failed[] = 'Profile: ' . ($profile['name'] ?? 'unknown');
                        }
                    } catch (\Exception $e) {
                        $failed[] = 'Profile: ' . ($profile['name'] ?? 'unknown') . ' - ' . $e->getMessage();
                    }
                }
            }

            // Restore IP Pools if present in backup
            if (isset($config['ip_pools']) && is_array($config['ip_pools'])) {
                foreach ($config['ip_pools'] as $pool) {
                    try {
                        if ($mikrotikService->createIpPool($router->id, $pool)) {
                            $restored[] = 'IP Pool: ' . ($pool['name'] ?? 'unknown');
                        } else {
                            $failed[] = 'IP Pool: ' . ($pool['name'] ?? 'unknown');
                        }
                    } catch (\Exception $e) {
                        $failed[] = 'IP Pool: ' . ($pool['name'] ?? 'unknown') . ' - ' . $e->getMessage();
                    }
                }
            }

            // Restore PPP Secrets (users) if present in backup
            if (isset($config['ppp_secrets']) && is_array($config['ppp_secrets'])) {
                foreach ($config['ppp_secrets'] as $secret) {
                    try {
                        // Create a copy with router_id to avoid modifying original array
                        $secretData = array_merge($secret, ['router_id' => $router->id]);
                        if ($mikrotikService->createPppoeUser($secretData)) {
                            $restored[] = 'PPP Secret: ' . ($secret['username'] ?? 'unknown');
                        } else {
                            $failed[] = 'PPP Secret: ' . ($secret['username'] ?? 'unknown');
                        }
                    } catch (\Exception $e) {
                        $failed[] = 'PPP Secret: ' . ($secret['username'] ?? 'unknown') . ' - ' . $e->getMessage();
                    }
                }
            }

            // Restore Queues if present in backup
            if (isset($config['queues']) && is_array($config['queues'])) {
                foreach ($config['queues'] as $queue) {
                    try {
                        if ($mikrotikService->createQueue($router->id, $queue)) {
                            $restored[] = 'Queue: ' . ($queue['name'] ?? 'unknown');
                        } else {
                            $failed[] = 'Queue: ' . ($queue['name'] ?? 'unknown');
                        }
                    } catch (\Exception $e) {
                        $failed[] = 'Queue: ' . ($queue['name'] ?? 'unknown') . ' - ' . $e->getMessage();
                    }
                }
            }

            // Restore Firewall Rules if present in backup
            if (isset($config['firewall_rules']) && is_array($config['firewall_rules'])) {
                foreach ($config['firewall_rules'] as $rule) {
                    try {
                        if ($mikrotikService->addFirewallRule($router->id, $rule)) {
                            $restored[] = 'Firewall Rule: ' . ($rule['chain'] ?? 'unknown');
                        } else {
                            $failed[] = 'Firewall Rule: ' . ($rule['chain'] ?? 'unknown');
                        }
                    } catch (\Exception $e) {
                        $failed[] = 'Firewall Rule: ' . ($rule['chain'] ?? 'unknown') . ' - ' . $e->getMessage();
                    }
                }
            }

            Log::info('Restore from backup completed', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'restored_count' => count($restored),
                'failed_count' => count($failed),
                'restored_items' => $restored,
                'failed_items' => $failed,
            ]);

            // Consider it successful only if no items failed to restore
            // (including the case where there were no items and no errors)
            return count($failed) === 0;

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
     * 
     * This method fetches current router configuration including profiles, pools, 
     * secrets, queues, and firewall rules using the MikrotikService.
     */
    protected function fetchRouterConfiguration(MikrotikRouter $router): ?string
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            // Connect to the router
            if (!$mikrotikService->connectRouter($router->id)) {
                Log::error('Failed to connect to router for backup', [
                    'router_id' => $router->id,
                ]);
                return null;
            }

            $configuration = [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'timestamp' => now()->toDateTimeString(),
            ];

            // Fetch PPP profiles
            try {
                $configuration['ppp_profiles'] = $mikrotikService->getProfiles($router->id);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch PPP profiles for backup', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $configuration['ppp_profiles'] = [];
            }

            // Fetch IP pools
            try {
                $configuration['ip_pools'] = $mikrotikService->importIpPools($router->id);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch IP pools for backup', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $configuration['ip_pools'] = [];
            }

            // Fetch PPP secrets
            try {
                $configuration['ppp_secrets'] = $mikrotikService->importSecrets($router->id);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch PPP secrets for backup', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $configuration['ppp_secrets'] = [];
            }

            // Fetch queues
            try {
                $configuration['queues'] = $mikrotikService->getQueues($router->id);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch queues for backup', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $configuration['queues'] = [];
            }

            // Fetch firewall rules
            try {
                $configuration['firewall_rules'] = $mikrotikService->getFirewallRules($router->id);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch firewall rules for backup', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
                $configuration['firewall_rules'] = [];
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
