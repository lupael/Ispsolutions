<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use App\Models\MikrotikPppoeUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MikroTik Service
 * 
 * SECURITY NOTES:
 * 1. Authentication: This implementation uses HTTP for the mock server. Real MikroTik routers
 *    require proper authentication. Router credentials from the database should be included
 *    in API requests for production use.
 * 2. Encryption: Passwords are transmitted over HTTP. For production, configure HTTPS with
 *    proper certificate validation to protect credentials in transit. Consider adding a
 *    configuration option to enforce HTTPS for production environments.
 * 3. Password Storage: Router and user passwords are encrypted at rest using Laravel's
 *    encrypted casting, but are decrypted when transmitted to the router.
 */
class MikrotikService implements MikrotikServiceInterface
{
    private ?MikrotikRouter $currentRouter = null;

    /**
     * @inheritDoc
     */
    public function connectRouter(int $routerId): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (!$router) {
                Log::error("Router not found", ['router_id' => $routerId]);
                return false;
            }

            // Test connection to router (using HTTP API for mock server)
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/health");

            if ($response->successful()) {
                $this->currentRouter = $router;
                Log::info("Connected to MikroTik router", ['router_id' => $routerId]);
                return true;
            }

            Log::warning("Failed to connect to MikroTik router", [
                'router_id' => $routerId,
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error connecting to MikroTik router", [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function createPppoeUser(array $userData): bool
    {
        try {
            $router = $this->getRouter($userData['router_id'] ?? null);
            
            if (!$router) {
                return false;
            }

            // Create user on MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/add", [
                    'name' => $userData['username'],
                    'password' => $userData['password'],
                    'service' => $userData['service'] ?? 'pppoe',
                    'profile' => $userData['profile'] ?? 'default',
                    'local-address' => $userData['local_address'] ?? '',
                    'remote-address' => $userData['remote_address'] ?? '',
                ]);

            if ($response->successful()) {
                // Store in local database
                MikrotikPppoeUser::create([
                    'router_id' => $router->id,
                    'username' => $userData['username'],
                    'password' => $userData['password'],
                    'service' => $userData['service'] ?? 'pppoe',
                    'profile' => $userData['profile'] ?? 'default',
                    'local_address' => $userData['local_address'] ?? null,
                    'remote_address' => $userData['remote_address'] ?? null,
                    'status' => 'synced',
                ]);

                Log::info("PPPoE user created on MikroTik", [
                    'router_id' => $router->id,
                    'username' => $userData['username'],
                ]);
                return true;
            }

            Log::error("Failed to create PPPoE user on MikroTik", [
                'router_id' => $router->id,
                'username' => $userData['username'],
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error creating PPPoE user", [
                'username' => $userData['username'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePppoeUser(string $username, array $userData): bool
    {
        try {
            $localUser = MikrotikPppoeUser::where('username', $username)->first();
            
            if (!$localUser) {
                Log::error("PPPoE user not found in local database", ['username' => $username]);
                return false;
            }

            $router = $localUser->router;

            // Update user on MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/set", [
                    'name' => $username,
                    'password' => $userData['password'] ?? $localUser->password,
                    'service' => $userData['service'] ?? $localUser->service,
                    'profile' => $userData['profile'] ?? $localUser->profile,
                    'local-address' => $userData['local_address'] ?? $localUser->local_address,
                    'remote-address' => $userData['remote_address'] ?? $localUser->remote_address,
                ]);

            if ($response->successful()) {
                // Update local database
                $localUser->update([
                    'password' => $userData['password'] ?? $localUser->password,
                    'service' => $userData['service'] ?? $localUser->service,
                    'profile' => $userData['profile'] ?? $localUser->profile,
                    'local_address' => $userData['local_address'] ?? $localUser->local_address,
                    'remote_address' => $userData['remote_address'] ?? $localUser->remote_address,
                    'status' => 'synced',
                ]);

                Log::info("PPPoE user updated on MikroTik", [
                    'router_id' => $router->id,
                    'username' => $username,
                ]);
                return true;
            }

            Log::error("Failed to update PPPoE user on MikroTik", [
                'router_id' => $router->id,
                'username' => $username,
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error updating PPPoE user", [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deletePppoeUser(string $username): bool
    {
        try {
            $localUser = MikrotikPppoeUser::where('username', $username)->first();
            
            if (!$localUser) {
                Log::error("PPPoE user not found in local database", ['username' => $username]);
                return false;
            }

            $router = $localUser->router;

            // Delete user from MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/remove", [
                    'name' => $username,
                ]);

            if ($response->successful()) {
                // Update local database status
                $localUser->update(['status' => 'inactive']);

                Log::info("PPPoE user deleted from MikroTik", [
                    'router_id' => $router->id,
                    'username' => $username,
                ]);
                return true;
            }

            Log::error("Failed to delete PPPoE user from MikroTik", [
                'router_id' => $router->id,
                'username' => $username,
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error deleting PPPoE user", [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getActiveSessions(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (!$router) {
                Log::error("Router not found", ['router_id' => $routerId]);
                return [];
            }

            // Get active sessions from MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ppp/active/print");

            if ($response->successful()) {
                $data = $response->json();
                return $data['sessions'] ?? [];
            }

            Log::error("Failed to get active sessions from MikroTik", [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error("Error getting active sessions", [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function disconnectSession(string $sessionId): bool
    {
        try {
            if (!$this->currentRouter) {
                Log::error("No router connected");
                return false;
            }

            // Disconnect session on MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$this->currentRouter->ip_address}:{$this->currentRouter->api_port}/api/ppp/active/remove", [
                    'id' => $sessionId,
                ]);

            if ($response->successful()) {
                Log::info("Session disconnected on MikroTik", [
                    'router_id' => $this->currentRouter->id,
                    'session_id' => $sessionId,
                ]);
                return true;
            }

            Log::error("Failed to disconnect session on MikroTik", [
                'router_id' => $this->currentRouter->id,
                'session_id' => $sessionId,
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error disconnecting session", [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get router instance
     */
    private function getRouter(?int $routerId): ?MikrotikRouter
    {
        if ($routerId) {
            return MikrotikRouter::find($routerId);
        }

        return $this->currentRouter;
    }
}
