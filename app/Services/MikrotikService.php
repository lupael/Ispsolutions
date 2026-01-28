<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikIpPool;
use App\Models\MikrotikPppoeUser;
use App\Models\MikrotikProfile;
use App\Models\MikrotikQueue;
use App\Models\MikrotikRouter;
use App\Models\MikrotikVpnAccount;
use App\Models\RouterConfiguration;
use Illuminate\Support\Facades\DB;
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
     * {@inheritDoc}
     */
    public function connectRouter(int $routerId): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            // Validate router IP to prevent SSRF attacks
            if (! $this->isValidRouterIpAddress($router->ip_address)) {
                Log::error('Router IP address validation failed - potential SSRF attempt', [
                    'router_id' => $routerId,
                    'ip_address' => $router->ip_address,
                ]);

                return false;
            }

            // Test connection to router (using HTTP API for mock server)
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/health");

            if ($response->successful()) {
                $this->currentRouter = $router;
                Log::info('Connected to MikroTik router', ['router_id' => $routerId]);

                return true;
            }

            Log::warning('Failed to connect to MikroTik router', [
                'router_id' => $routerId,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error connecting to MikroTik router', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createPppoeUser(array $userData): bool
    {
        try {
            $router = $this->getRouter($userData['router_id'] ?? null);

            if (! $router) {
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

                Log::info('PPPoE user created on MikroTik', [
                    'router_id' => $router->id,
                    'username' => $userData['username'],
                ]);

                return true;
            }

            Log::error('Failed to create PPPoE user on MikroTik', [
                'router_id' => $router->id,
                'username' => $userData['username'],
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error creating PPPoE user', [
                'username' => $userData['username'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function updatePppoeUser(string $username, array $userData): bool
    {
        try {
            $localUser = MikrotikPppoeUser::where('username', $username)->first();

            if (! $localUser) {
                Log::error('PPPoE user not found in local database', ['username' => $username]);

                return false;
            }

            /** @var MikrotikRouter $router */
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

                Log::info('PPPoE user updated on MikroTik', [
                    'router_id' => $router->id,
                    'username' => $username,
                ]);

                return true;
            }

            Log::error('Failed to update PPPoE user on MikroTik', [
                'router_id' => $router->id,
                'username' => $username,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error updating PPPoE user', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deletePppoeUser(string $username): bool
    {
        try {
            $localUser = MikrotikPppoeUser::where('username', $username)->first();

            if (! $localUser) {
                Log::error('PPPoE user not found in local database', ['username' => $username]);

                return false;
            }

            /** @var MikrotikRouter $router */
            $router = $localUser->router;

            // Delete user from MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/remove", [
                    'name' => $username,
                ]);

            if ($response->successful()) {
                // Update local database status
                $localUser->update(['status' => 'inactive']);

                Log::info('PPPoE user deleted from MikroTik', [
                    'router_id' => $router->id,
                    'username' => $username,
                ]);

                return true;
            }

            Log::error('Failed to delete PPPoE user from MikroTik', [
                'router_id' => $router->id,
                'username' => $username,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error deleting PPPoE user', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveSessions(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Get active sessions from MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ppp/active/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['sessions'] ?? [];
            }

            Log::error('Failed to get active sessions from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting active sessions', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function disconnectSession(string $sessionId): bool
    {
        try {
            if (! $this->currentRouter) {
                Log::error('No router connected');

                return false;
            }

            // Disconnect session on MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$this->currentRouter->ip_address}:{$this->currentRouter->api_port}/api/ppp/active/remove", [
                    'id' => $sessionId,
                ]);

            if ($response->successful()) {
                Log::info('Session disconnected on MikroTik', [
                    'router_id' => $this->currentRouter->id,
                    'session_id' => $sessionId,
                ]);

                return true;
            }

            Log::error('Failed to disconnect session on MikroTik', [
                'router_id' => $this->currentRouter->id,
                'session_id' => $sessionId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error disconnecting session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProfiles(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Get PPPoE profiles from MikroTik via API
            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ppp/profile/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['profiles'] ?? [];
            }

            Log::error('Failed to get profiles from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting profiles', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
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

    /**
     * {@inheritDoc}
     */
    public function createPppProfile(int $routerId, array $profileData): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/profile/add", [
                    'name' => $profileData['name'],
                    'local-address' => $profileData['local_address'] ?? '',
                    'remote-address' => $profileData['remote_address'] ?? '',
                    'rate-limit' => $profileData['rate_limit'] ?? '',
                    'session-timeout' => $profileData['session_timeout'] ?? 0,
                    'idle-timeout' => $profileData['idle_timeout'] ?? 0,
                ]);

            if ($response->successful()) {
                MikrotikProfile::updateOrCreate(
                    [
                        'router_id' => $routerId,
                        'name' => $profileData['name'],
                    ],
                    [
                        'local_address' => $profileData['local_address'] ?? null,
                        'remote_address' => $profileData['remote_address'] ?? null,
                        'rate_limit' => $profileData['rate_limit'] ?? null,
                        'session_timeout' => $profileData['session_timeout'] ?? null,
                        'idle_timeout' => $profileData['idle_timeout'] ?? null,
                    ]
                );

                Log::info('PPPoE profile created', [
                    'router_id' => $routerId,
                    'profile' => $profileData['name'],
                ]);

                return true;
            }

            Log::error('Failed to create profile on MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error creating PPPoE profile', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importProfiles(int $routerId): array
    {
        try {
            $profiles = $this->getProfiles($routerId);

            if (empty($profiles)) {
                return [];
            }

            return $profiles;
        } catch (\Exception $e) {
            Log::error('Error importing profiles', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function syncProfiles(int $routerId): int
    {
        try {
            $profiles = $this->importProfiles($routerId);

            if (empty($profiles)) {
                return 0;
            }

            $synced = 0;

            DB::beginTransaction();

            foreach ($profiles as $profile) {
                MikrotikProfile::updateOrCreate(
                    [
                        'router_id' => $routerId,
                        'name' => $profile['name'] ?? '',
                    ],
                    [
                        'local_address' => $profile['local-address'] ?? null,
                        'remote_address' => $profile['remote-address'] ?? null,
                        'rate_limit' => $profile['rate-limit'] ?? null,
                        'session_timeout' => $profile['session-timeout'] ?? null,
                        'idle_timeout' => $profile['idle-timeout'] ?? null,
                    ]
                );

                $synced++;
            }

            DB::commit();

            Log::info('Profiles synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error syncing profiles', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createIpPool(int $routerId, array $poolData): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $ranges = is_array($poolData['ranges']) ? implode(',', $poolData['ranges']) : $poolData['ranges'];

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/pool/add", [
                    'name' => $poolData['name'],
                    'ranges' => $ranges,
                ]);

            if ($response->successful()) {
                MikrotikIpPool::updateOrCreate(
                    [
                        'router_id' => $routerId,
                        'name' => $poolData['name'],
                    ],
                    [
                        'ranges' => is_array($poolData['ranges']) ? $poolData['ranges'] : [$poolData['ranges']],
                    ]
                );

                Log::info('IP pool created', [
                    'router_id' => $routerId,
                    'pool' => $poolData['name'],
                ]);

                return true;
            }

            Log::error('Failed to create IP pool on MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error creating IP pool', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importIpPools(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ip/pool/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['pools'] ?? [];
            }

            Log::error('Failed to import IP pools from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error importing IP pools', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function syncIpPools(int $routerId): int
    {
        try {
            $pools = $this->importIpPools($routerId);

            if (empty($pools)) {
                return 0;
            }

            $synced = 0;

            DB::beginTransaction();

            foreach ($pools as $pool) {
                $ranges = isset($pool['ranges']) ? (is_array($pool['ranges']) ? $pool['ranges'] : [$pool['ranges']]) : [];

                MikrotikIpPool::updateOrCreate(
                    [
                        'router_id' => $routerId,
                        'name' => $pool['name'] ?? '',
                    ],
                    [
                        'ranges' => $ranges,
                    ]
                );

                $synced++;
            }

            DB::commit();

            Log::info('IP pools synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error syncing IP pools', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importSecrets(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['secrets'] ?? [];
            }

            Log::error('Failed to import secrets from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error importing secrets', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function syncSecrets(int $routerId): int
    {
        try {
            $secrets = $this->importSecrets($routerId);

            if (empty($secrets)) {
                return 0;
            }

            $synced = 0;

            DB::beginTransaction();

            foreach ($secrets as $secret) {
                MikrotikPppoeUser::updateOrCreate(
                    [
                        'router_id' => $routerId,
                        'username' => $secret['name'] ?? '',
                    ],
                    [
                        'password' => $secret['password'] ?? '',
                        'service' => $secret['service'] ?? 'pppoe',
                        'profile' => $secret['profile'] ?? 'default',
                        'local_address' => $secret['local-address'] ?? null,
                        'remote_address' => $secret['remote-address'] ?? null,
                        'status' => 'synced',
                    ]
                );

                $synced++;
            }

            DB::commit();

            Log::info('Secrets synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error syncing secrets', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureRouter(int $routerId, array $config): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            // Validate router IP to prevent SSRF attacks
            if (! $this->isValidRouterIpAddress($router->ip_address)) {
                Log::error('Router IP address validation failed - potential SSRF attempt', [
                    'router_id' => $routerId,
                    'ip_address' => $router->ip_address,
                ]);

                return false;
            }

            DB::beginTransaction();

            $response = Http::timeout(config('services.mikrotik.timeout', 60))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/configure", $config);

            if ($response->successful()) {
                RouterConfiguration::create([
                    'router_id' => $routerId,
                    'config_type' => 'one-click',
                    'config_data' => $config,
                    'applied_at' => now(),
                    'status' => 'applied',
                ]);

                DB::commit();

                Log::info('Router configured', [
                    'router_id' => $routerId,
                    'config_types' => array_keys($config),
                ]);

                return true;
            }

            DB::rollBack();

            Log::error('Failed to configure router', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error configuring router', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createVpnAccount(int $routerId, array $vpnData): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/secret/add", [
                    'name' => $vpnData['username'],
                    'password' => $vpnData['password'],
                    'service' => $vpnData['service'] ?? 'l2tp',
                    'profile' => $vpnData['profile'] ?? 'default',
                ]);

            if ($response->successful()) {
                MikrotikVpnAccount::create([
                    'router_id' => $routerId,
                    'username' => $vpnData['username'],
                    'password' => $vpnData['password'],
                    'profile' => $vpnData['profile'] ?? 'default',
                    'enabled' => $vpnData['enabled'] ?? true,
                ]);

                Log::info('VPN account created', [
                    'router_id' => $routerId,
                    'username' => $vpnData['username'],
                ]);

                return true;
            }

            Log::error('Failed to create VPN account on MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error creating VPN account', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getVpnStatus(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/interface/l2tp-server/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['servers'] ?? [];
            }

            Log::error('Failed to get VPN status from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting VPN status', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue(int $routerId, array $queueData): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/queue/simple/add", [
                    'name' => $queueData['name'],
                    'target' => $queueData['target'],
                    'parent' => $queueData['parent'] ?? 'none',
                    'max-limit' => $queueData['max_limit'] ?? '',
                    'burst-limit' => $queueData['burst_limit'] ?? '',
                    'burst-threshold' => $queueData['burst_threshold'] ?? '',
                    'burst-time' => $queueData['burst_time'] ?? 0,
                    'priority' => $queueData['priority'] ?? 8,
                ]);

            if ($response->successful()) {
                MikrotikQueue::create([
                    'router_id' => $routerId,
                    'name' => $queueData['name'],
                    'target' => $queueData['target'],
                    'parent' => $queueData['parent'] ?? null,
                    'max_limit' => $queueData['max_limit'] ?? null,
                    'burst_limit' => $queueData['burst_limit'] ?? null,
                    'burst_threshold' => $queueData['burst_threshold'] ?? null,
                    'burst_time' => $queueData['burst_time'] ?? null,
                    'priority' => $queueData['priority'] ?? 8,
                ]);

                Log::info('Queue created', [
                    'router_id' => $routerId,
                    'queue' => $queueData['name'],
                ]);

                return true;
            }

            Log::error('Failed to create queue on MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error creating queue', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getQueues(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/queue/simple/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['queues'] ?? [];
            }

            Log::error('Failed to get queues from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting queues', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addFirewallRule(int $routerId, array $ruleData): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/filter/add", $ruleData);

            if ($response->successful()) {
                Log::info('Firewall rule added', [
                    'router_id' => $routerId,
                    'chain' => $ruleData['chain'] ?? 'forward',
                ]);

                return true;
            }

            Log::error('Failed to add firewall rule on MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error adding firewall rule', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFirewallRules(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $response = Http::timeout(config('services.mikrotik.timeout', 30))
                ->get("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/filter/print");

            if ($response->successful()) {
                $data = $response->json();

                return $data['rules'] ?? [];
            }

            Log::error('Failed to get firewall rules from MikroTik', [
                'router_id' => $routerId,
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting firewall rules', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Validate router IP address to prevent SSRF attacks
     *
     * This method checks if the router IP address is safe to connect to
     * by blocking private IP ranges, localhost, and other potentially
     * dangerous addresses.
     *
     * @param string $ipAddress The IP address to validate
     *
     * @return bool True if the IP is safe, false otherwise
     */
    private function isValidRouterIpAddress(string $ipAddress): bool
    {
        // Allow localhost for testing/development
        if (in_array($ipAddress, ['localhost', '127.0.0.1', '::1'])) {
            // Only allow in non-production environments
            return config('app.env') !== 'production';
        }

        // Validate IP format
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            Log::warning('Invalid IP address format', ['ip' => $ipAddress]);

            return false;
        }

        // Block private IP ranges to prevent SSRF to internal network
        // Allow configuration override for legitimate internal routers
        $allowPrivateIps = config('services.mikrotik.allow_private_ips', false);

        if (! $allowPrivateIps) {
            // Check if IP is in private ranges
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                Log::warning('Blocked connection to private/reserved IP address', [
                    'ip' => $ipAddress,
                    'hint' => 'Set services.mikrotik.allow_private_ips=true in config to allow internal IPs',
                ]);

                return false;
            }
        }

        return true;
    }
}
