<?php

namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Support\Facades\Log;

/**
 * RouterManager Service
 *
 * Manages router configurations, backups, and operations.
 * This service acts as a facade/dispatcher to vendor-specific services.
 */
class RouterManager
{
    public function __construct(
        private MikrotikService $mikrotikService
    ) {}

    /**
     * Apply configuration to a router.
     *
     * @throws \Exception
     */
    public function applyConfiguration(int $routerId, array $config): bool
    {
        Log::info("RouterManager: Applying configuration to router {$routerId}", $config);

        // Determine router vendor and dispatch to appropriate service
        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            throw new \Exception("Router not found: {$routerId}");
        }

        // For now, we only support MikroTik routers
        // In the future, add support for other vendors (Cisco, Juniper, etc.)
        if ($this->mikrotikService->connectRouter($routerId)) {
            // Apply configuration through MikroTik service
            // This is a placeholder - actual configuration application depends on what's in $config
            Log::info("Configuration would be applied to MikroTik router {$routerId}");
            return true;
        }

        return false;
    }

    /**
     * Backup router configuration.
     *
     * @throws \Exception
     */
    public function backupConfiguration(int $routerId): ?string
    {
        Log::info("RouterManager: Backing up configuration for router {$routerId}");

        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            throw new \Exception("Router not found: {$routerId}");
        }

        // For MikroTik, use the backup functionality
        if ($this->mikrotikService->connectRouter($routerId)) {
            // The backup is already handled by scheduled jobs
            // Return a status message
            return "Backup scheduled for router {$routerId}";
        }

        return null;
    }

    /**
     * Test router connectivity by router ID.
     */
    public function testConnection(int $routerId): bool
    {
        Log::info("RouterManager: Testing connection to router {$routerId}");

        try {
            return $this->mikrotikService->connectRouter($routerId);
        } catch (\Exception $e) {
            Log::error("Connection test failed", ['router_id' => $routerId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get router resource usage.
     */
    public function getResourceUsage(int $routerId): array
    {
        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            return [];
        }

        // For MikroTik, return basic health status
        // Full health monitoring would require additional API implementation
        if ($this->mikrotikService->connectRouter($routerId)) {
            return [
                'status' => 'connected',
                'router_id' => $routerId,
            ];
        }

        return [];
    }

    /**
     * Reboot router.
     *
     * @throws \BadMethodCallException Always, as this method is not implemented for safety
     */
    public function reboot(int $routerId): bool
    {
        throw new \BadMethodCallException(
            'Router reboot is intentionally not implemented for safety reasons. ' .
            'Please reboot routers manually through their admin interfaces.'
        );
    }

    /**
     * Get active sessions from router.
     */
    public function getActiveSessions(int $routerId): array
    {
        Log::info("RouterManager: Getting active sessions for router {$routerId}");

        if ($this->mikrotikService->connectRouter($routerId)) {
            return $this->mikrotikService->getActiveSessions($routerId);
        }

        return [];
    }

    /**
     * Disconnect a user session.
     */
    public function disconnectSession(int $routerId, string $sessionId): bool
    {
        Log::info("RouterManager: Disconnecting session {$sessionId} on router {$routerId}");

        if ($this->mikrotikService->connectRouter($routerId)) {
            // MikrotikService::disconnectSession only needs sessionId
            return $this->mikrotikService->disconnectSession($sessionId);
        }

        return false;
    }

    /**
     * Create PPPoE users on router (batch operation).
     * 
     * Note: This only creates new users. For true sync (including updates/deletions),
     * use createPPPoEUser(), updatePPPoEUser(), or deletePPPoEUser() separately.
     * 
     * @return array{success: int, failed: int, errors: array<string>}
     */
    public function syncUsers(int $routerId, array $users): array
    {
        Log::info('RouterManager: Creating ' . count($users) . " PPPoE users on router {$routerId}");

        if (!$this->mikrotikService->connectRouter($routerId)) {
            return [
                'success' => 0,
                'failed' => count($users),
                'errors' => ['Failed to connect to router'],
            ];
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            $username = $user['username'] ?? 'unknown';
            if ($this->mikrotikService->createPppoeUser($user)) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = "Failed to create user: {$username}";
                Log::error("Failed to create PPPoE user", ['user' => $username, 'router_id' => $routerId]);
            }
        }

        Log::info("PPPoE user creation complete", [
            'router_id' => $routerId,
            'success' => $successCount,
            'failed' => $failedCount,
        ]);

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Create PPPoE user on router.
     */
    public function createPPPoEUser(int $routerId, array $userData): bool
    {
        Log::info("RouterManager: Creating PPPoE user on router {$routerId}", $userData);

        $userData['router_id'] = $routerId;
        return $this->mikrotikService->createPppoeUser($userData);
    }

    /**
     * Update PPPoE user on router.
     */
    public function updatePPPoEUser(int $routerId, string $username, array $userData): bool
    {
        Log::info("RouterManager: Updating PPPoE user {$username} on router {$routerId}", $userData);

        $userData['router_id'] = $routerId;
        $userData['username'] = $username;
        return $this->mikrotikService->updatePppoeUser($userData);
    }

    /**
     * Delete PPPoE user from router.
     */
    public function deletePPPoEUser(int $routerId, string $username): bool
    {
        Log::info("RouterManager: Deleting PPPoE user {$username} from router {$routerId}");

        return $this->mikrotikService->deletePppoeUser($routerId, $username);
    }
}
