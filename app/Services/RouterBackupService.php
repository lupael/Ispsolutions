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
                'tenant_id' => $router->tenant_id,
                'router_id' => $router->id,
                'created_by' => $userId,
                'backup_type' => 'pre_change',
                'backup_name' => 'Pre-change backup: ' . $reason,
                'backup_reason' => $reason,
                'backup_data' => $backupData,
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
    public function createManualBackup(MikrotikRouter $router, string $name, ?string $reason = null, ?int $userId = null): ?RouterConfigurationBackup
    {
        try {
            $backupData = $this->fetchRouterConfiguration($router);
            
            if (!$backupData) {
                return null;
            }

            return RouterConfigurationBackup::create([
                'tenant_id' => $router->tenant_id,
                'router_id' => $router->id,
                'created_by' => $userId,
                'backup_type' => 'manual',
                'backup_name' => $name,
                'backup_reason' => $reason,
                'backup_data' => $backupData,
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
            null
        );
    }

    /**
     * Backup PPP secrets from router
     */
    public function backupPppSecrets(MikrotikRouter $router): ?string
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return null;
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return null;
            }

            // Fetch all PPP secrets
            $secrets = $api->comm('/ppp/secret/print');
            
            // Convert to JSON for storage
            return json_encode($secrets, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('PPP secrets backup failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mirror customers from database to router (sync all users)
     */
    public function mirrorCustomersToRouter(MikrotikRouter $router): array
    {
        try {
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to router',
                    'synced' => 0,
                    'failed' => 0,
                ];
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return [
                    'success' => false,
                    'error' => 'Router connection not available',
                    'synced' => 0,
                    'failed' => 0,
                ];
            }

            // Get all active PPPoE users from database
            $networkUsers = \App\Models\NetworkUser::where('tenant_id', $router->tenant_id)
                ->where('is_active', true)
                ->where('service_type', 'pppoe')
                ->with('package')
                ->get();

            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($networkUsers as $user) {
                try {
                    // Check if secret exists on router
                    $existing = $api->comm('/ppp/secret/print', [
                        '?name' => $user->username,
                    ]);

                    $profile = $user->package?->name ?? 'default';
                    
                    if (empty($existing)) {
                        // Create new secret
                        $api->comm('/ppp/secret/add', [
                            'name' => $user->username,
                            'password' => $user->password,
                            'service' => 'pppoe',
                            'profile' => $profile,
                            'comment' => \App\Helpers\RouterCommentHelper::buildUserComment($user),
                        ]);
                    } else {
                        // Update existing secret
                        $api->comm('/ppp/secret/set', [
                            '.id' => $existing[0]['.id'],
                            'password' => $user->password,
                            'profile' => $profile,
                            'comment' => \App\Helpers\RouterCommentHelper::buildUserComment($user),
                        ]);
                    }
                    
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "User {$user->username}: " . $e->getMessage();
                    Log::error('Failed to sync user to router', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'router_id' => $router->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'total' => $networkUsers->count(),
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('Mirror customers to router failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'synced' => 0,
                'failed' => 0,
            ];
        }
    }

    /**
     * Restore configuration from backup
     * 
     * Note: This is a placeholder implementation. Full restore functionality requires:
     * 1. Parsing the backup JSON data structure
     * 2. Applying configurations in the correct order via MikroTik API
     * 3. Handling conflicts and validation
     * This should be implemented based on specific backup format and requirements.
     */
    public function restoreFromBackup(MikrotikRouter $router, RouterConfigurationBackup $backup): bool
    {
        try {
            if ($backup->router_id !== $router->id) {
                throw new \Exception('Backup does not belong to this router');
            }

            // Implementation depends on router API
            Log::warning('Restore from backup called - placeholder implementation', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'backup_name' => $backup->backup_name,
            ]);

            // TODO: Implement actual restore logic via MikroTik API
            // This would involve:
            // 1. Parsing $backup->backup_data JSON
            // 2. Applying each configuration section via MikroTik API
            // 3. Validating the restored configuration
            
            throw new \Exception('Restore from backup is not yet implemented');
        } catch (\Exception $e) {
            Log::error('Restore from backup failed', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
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
            $mikrotikService = app(MikrotikService::class);
            
            if (!$mikrotikService->connectRouter($router->id)) {
                return null;
            }

            $api = $mikrotikService->getConnectedRouter($router->id);
            if (!$api) {
                return null;
            }

            // Fetch various configuration sections
            $config = [
                'system' => $api->comm('/system/identity/print'),
                'interfaces' => $api->comm('/interface/print'),
                'ip_pools' => $api->comm('/ip/pool/print'),
                'ppp_profiles' => $api->comm('/ppp/profile/print'),
                'ppp_secrets' => $api->comm('/ppp/secret/print'),
                'radius' => $api->comm('/radius/print'),
                'timestamp' => now()->toDateTimeString(),
            ];

            return json_encode($config, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to fetch router configuration', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
