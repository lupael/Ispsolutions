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
     * Test router connectivity.
     */
    public function testConnection(string $host, int $port = 8728, string $username = '', string $password = ''): bool
    {
        Log::info("RouterManager: Testing connection to {$host}:{$port}");

        try {
            // Try to find router by host
            $router = MikrotikRouter::where('ip_address', $host)->first();
            
            if ($router) {
                return $this->mikrotikService->connectRouter($router->id);
            }

            // If router not found in database, log warning
            Log::warning("Router not found in database", ['host' => $host]);
            return false;
        } catch (\Exception $e) {
            Log::error("Connection test failed", ['host' => $host, 'error' => $e->getMessage()]);
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

        // For MikroTik, use health monitoring
        if ($this->mikrotikService->connectRouter($routerId)) {
            return $this->mikrotikService->monitorRouterHealth($routerId);
        }

        return [];
    }

    /**
     * Reboot router.
     *
     * @throws \Exception
     */
    public function reboot(int $routerId): bool
    {
        Log::warning("RouterManager: Rebooting router {$routerId}");

        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            throw new \Exception("Router not found: {$routerId}");
        }

        // This is a dangerous operation and should be used with caution
        // For now, just log and return false
        Log::warning("Router reboot requested but not implemented for safety", ['router_id' => $routerId]);
        return false;
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
            return $this->mikrotikService->disconnectSession($routerId, $sessionId);
        }

        return false;
    }

    /**
     * Sync user accounts to router.
     */
    public function syncUsers(int $routerId, array $users): bool
    {
        Log::info('RouterManager: Syncing ' . count($users) . " users to router {$routerId}");

        if (!$this->mikrotikService->connectRouter($routerId)) {
            return false;
        }

        $success = true;
        foreach ($users as $user) {
            if (!$this->mikrotikService->createPppoeUser($user)) {
                $success = false;
                Log::error("Failed to sync user", ['user' => $user['username'] ?? 'unknown']);
            }
        }

        return $success;
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
