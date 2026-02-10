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
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

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

    protected MikrotikApiService $mikrotikApiService;

    public function __construct(MikrotikApiService $mikrotikApiService)
    {
        $this->mikrotikApiService = $mikrotikApiService;
    }

    /**
     * {@inheritDoc}
     */
    public function connectRouter(int $routerId): bool
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            // Validate router IP to prevent SSRF attacks
            if (! $this->isValidRouterIpAddress($router->ip_address)) {
                Log::channel('device_operations')->error('Router IP address validation failed - potential SSRF attempt', [
                    'router_id' => $routerId,
                    'ip_address' => $router->ip_address,
                ]);

                return false;
            }

            // Test connection to router using Binary API
            $client = $this->createClient($router);
            $query = new Query('/system/identity/print');
            $client->query($query)->read();

            $this->currentRouter = $router;
            Log::channel('device_operations')->info('Connected to MikroTik router via Binary API', ['router_id' => $routerId]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error connecting to MikroTik router', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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

            // Create user on MikroTik via Binary API
            $client = $this->createClient($router);
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $userData['username'])
                ->equal('password', $userData['password'])
                ->equal('service', $userData['service'] ?? 'pppoe')
                ->equal('profile', $userData['profile'] ?? 'default');

            if (!empty($userData['local_address'])) {
                $query->equal('local-address', $userData['local_address']);
            }
            if (!empty($userData['remote_address'])) {
                $query->equal('remote-address', $userData['remote_address']);
            }

            $client->query($query)->read();

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

            Log::channel('device_operations')->info('PPPoE user created on MikroTik via Binary API', [
                'router_id' => $router->id,
                'username' => $userData['username'],
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout while creating PPPoE user', [
                'router_id' => $userData['router_id'] ?? null,
                'username' => $userData['username'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error creating PPPoE user', [
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
                Log::channel('device_operations')->error('PPPoE user not found in local database', ['username' => $username]);

                return false;
            }

            /** @var MikrotikRouter $router */
            $router = $localUser->router;

            // Update user on MikroTik via Binary API
            $client = $this->createClient($router);

            // First, find the secret by name to get its .id
            $findQuery = (new Query('/ppp/secret/print'))
                ->equal('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (empty($secrets)) {
                Log::channel('device_operations')->error('PPPoE user not found on MikroTik router', [
                    'router_id' => $router->id,
                    'router_name' => $router->name,
                    'username' => $username,
                ]);
                return false;
            }

            // Update using the .id
            $setQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('password', $userData['password'] ?? $localUser->password)
                ->equal('service', $userData['service'] ?? $localUser->service)
                ->equal('profile', $userData['profile'] ?? $localUser->profile)
                ->equal('local-address', $userData['local_address'] ?? $localUser->local_address)
                ->equal('remote-address', $userData['remote_address'] ?? $localUser->remote_address);

            $client->query($setQuery)->read();

            // Update local database
            $localUser->update([
                'password' => $userData['password'] ?? $localUser->password,
                'service' => $userData['service'] ?? $localUser->service,
                'profile' => $userData['profile'] ?? $localUser->profile,
                'local_address' => $userData['local_address'] ?? $localUser->local_address,
                'remote_address' => $userData['remote_address'] ?? $localUser->remote_address,
                'status' => 'synced',
            ]);

            Log::channel('device_operations')->info('PPPoE user updated on MikroTik', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'username' => $username,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while updating PPPoE user', [
                'router_id' => $localUser->router->id ?? null,
                'router_name' => $localUser->router->name ?? 'Unknown',
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error updating PPPoE user', [
                'router_id' => $localUser->router->id ?? null,
                'router_name' => $localUser->router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('PPPoE user not found in local database', ['username' => $username]);

                return false;
            }

            /** @var MikrotikRouter $router */
            $router = $localUser->router;

            // Delete user from MikroTik via Binary API
            $client = $this->createClient($router);

            // First, find the secret by name to get its .id
            $findQuery = (new Query('/ppp/secret/print'))
                ->equal('name', $username);
            $secrets = $client->query($findQuery)->read();

            if (empty($secrets)) {
                Log::channel('device_operations')->warning('PPPoE user not found on MikroTik router for deletion', [
                    'router_id' => $router->id,
                    'router_name' => $router->name,
                    'username' => $username,
                ]);
                // Still update local database as user doesn't exist on router
                $localUser->update(['status' => 'inactive']);
                return true;
            }

            // Remove using the .id
            $removeQuery = (new Query('/ppp/secret/remove'))
                ->equal('.id', $secrets[0]['.id']);

            $client->query($removeQuery)->read();

            // Update local database status
            $localUser->update(['status' => 'inactive']);

            Log::channel('device_operations')->info('PPPoE user deleted from MikroTik', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'username' => $username,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while deleting PPPoE user', [
                'router_id' => $localUser->router->id ?? null,
                'router_name' => $localUser->router->name ?? 'Unknown',
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error deleting PPPoE user', [
                'router_id' => $localUser->router->id ?? null,
                'router_name' => $localUser->router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Get active sessions from MikroTik via Binary API
            $client = $this->createClient($router);
            $query = new Query('/ppp/active/print');
            $response = $client->query($query)->read();

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while getting active sessions', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting active sessions', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('No router connected');

                return false;
            }

            // Disconnect session on MikroTik via Binary API
            $client = $this->createClient($this->currentRouter);
            $query = (new Query('/ppp/active/remove'))
                ->equal('.id', $sessionId);

            $client->query($query)->read();

            Log::channel('device_operations')->info('Session disconnected on MikroTik', [
                'router_id' => $this->currentRouter->id,
                'router_name' => $this->currentRouter->name,
                'session_id' => $sessionId,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while disconnecting session', [
                'router_id' => $this->currentRouter->id ?? null,
                'router_name' => $this->currentRouter->name ?? 'Unknown',
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error disconnecting session', [
                'router_id' => $this->currentRouter->id ?? null,
                'router_name' => $this->currentRouter->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Get PPPoE profiles from MikroTik via Binary API
            $client = $this->createClient($router);
            $query = new Query('/ppp/profile/print');
            $response = $client->query($query)->read();

            Log::channel('device_operations')->info('Successfully fetched profiles from MikroTik via Binary API', [
                'router_id' => $routerId,
                'count' => count($response),
            ]);

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout while getting profiles', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting profiles', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $client = $this->createClient($router);
            $query = (new Query('/ppp/profile/add'))
                ->equal('name', $profileData['name'])
                ->equal('local-address', $profileData['local_address'] ?? '')
                ->equal('remote-address', $profileData['remote_address'] ?? '')
                ->equal('rate-limit', $profileData['rate_limit'] ?? '')
                ->equal('session-timeout', isset($profileData['session_timeout']) ? (string) $profileData['session_timeout'] : '0')
                ->equal('idle-timeout', isset($profileData['idle_timeout']) ? (string) $profileData['idle_timeout'] : '0');

            $client->query($query)->read();

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

            Log::channel('device_operations')->info('PPPoE profile created', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'profile' => $profileData['name'],
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while creating PPPoE profile', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error creating PPPoE profile', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
            Log::channel('device_operations')->error('Error importing profiles', [
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

            Log::channel('device_operations')->info('Profiles synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('device_operations')->error('Error syncing profiles', [
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $ranges = is_array($poolData['ranges']) ? implode(',', $poolData['ranges']) : $poolData['ranges'];

            $client = $this->createClient($router);
            $query = (new Query('/ip/pool/add'))
                ->equal('name', $poolData['name'])
                ->equal('ranges', $ranges);

            $client->query($query)->read();

            MikrotikIpPool::updateOrCreate(
                [
                    'router_id' => $routerId,
                    'name' => $poolData['name'],
                ],
                [
                    'ranges' => is_array($poolData['ranges']) ? $poolData['ranges'] : [$poolData['ranges']],
                ]
            );

            Log::channel('device_operations')->info('IP pool created', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'pool' => $poolData['name'],
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while creating IP pool', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error creating IP pool', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Use Binary API to get IP pools
            $client = $this->createClient($router);
            $query = new Query('/ip/pool/print');
            $response = $client->query($query)->read();

            Log::channel('device_operations')->info('Successfully imported IP pools from MikroTik via Binary API', [
                'router_id' => $routerId,
                'count' => count($response),
            ]);

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout while importing IP pools', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error importing IP pools', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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

            Log::channel('device_operations')->info('IP pools synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('device_operations')->error('Error syncing IP pools', [
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Use Binary API to get PPP secrets
            $client = $this->createClient($router);
            $query = new Query('/ppp/secret/print');
            $response = $client->query($query)->read();

            Log::channel('device_operations')->info('Successfully imported secrets from MikroTik via Binary API', [
                'router_id' => $routerId,
                'count' => count($response),
            ]);

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout while importing secrets', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error importing secrets', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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

            Log::channel('device_operations')->info('Secrets synced', [
                'router_id' => $routerId,
                'count' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('device_operations')->error('Error syncing secrets', [
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            // Validate router IP to prevent SSRF attacks
            if (! $this->isValidRouterIpAddress($router->ip_address)) {
                Log::channel('device_operations')->error('Router IP address validation failed - potential SSRF attempt', [
                    'router_id' => $routerId,
                    'ip_address' => $router->ip_address,
                ]);

                return false;
            }

            DB::beginTransaction();

            // Apply configuration based on type using MikroTik API
            // Track configurations for potential rollback
            $appliedConfigs = [];
            $configDetails = [];

            foreach ($config as $configType => $settings) {
                $result = $this->applyConfiguration($router, $configType, $settings);
                if ($result) {
                    $appliedConfigs[] = $configType;
                    $configDetails[$configType] = $settings;
                } else {
                    // Configuration failed - rollback all previously applied configurations
                    Log::channel('device_operations')->error('Configuration failed, rolling back previously applied configurations', [
                        'router_id' => $routerId,
                        'failed_config_type' => $configType,
                        'applied_configs' => $appliedConfigs,
                    ]);

                    // Attempt to rollback (note: this is best-effort, may not always succeed)
                    foreach ($appliedConfigs as $appliedType) {
                        try {
                            $this->rollbackConfiguration($router, $appliedType, $configDetails[$appliedType]);
                        } catch (\Exception $rollbackException) {
                            Log::channel('device_operations')->error('Failed to rollback configuration', [
                                'router_id' => $router->id,
                                'config_type' => $appliedType,
                                'error' => $rollbackException->getMessage(),
                            ]);
                        }
                    }

                    DB::rollBack();

                    Log::channel('device_operations')->error('Failed to configure router - configuration aborted and rolled back', [
                        'router_id' => $routerId,
                        'failed_at' => $configType,
                        'rolled_back' => $appliedConfigs,
                    ]);

                    return false;
                }
            }

            // All configurations applied successfully
            RouterConfiguration::create([
                'router_id' => $routerId,
                'config_type' => 'one-click',
                'config_data' => $config,
                'applied_at' => now(),
                'status' => 'applied',
            ]);

            DB::commit();

            Log::channel('device_operations')->info('Router configured successfully', [
                'router_id' => $routerId,
                'config_types' => $appliedConfigs,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('device_operations')->error('Error configuring router', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Attempt to rollback a configuration change.
     * Note: This is best-effort and may not always succeed.
     */
    private function rollbackConfiguration(MikrotikRouter $router, string $configType, array $settings): void
    {
        Log::channel('device_operations')->info('Attempting to rollback configuration', [
            'router_id' => $router->id,
            'config_type' => $configType,
        ]);

        // Rollback logic varies by configuration type
        // For now, we log the attempt; full rollback implementation would require
        // storing previous state or removing added configurations
        switch ($configType) {
            case 'pppoe':
                // Would need to remove the PPPoE server we added
                // Requires tracking what was added in applyConfiguration
                break;
            case 'ippool':
                // Would need to remove the IP pool we added
                break;
            case 'firewall':
                // Would need to remove the firewall rule we added
                break;
            case 'queue':
                // Would need to remove the queue we added
                break;
        }

        Log::channel('device_operations')->warning('Rollback not fully implemented for configuration type', [
            'router_id' => $router->id,
            'config_type' => $configType,
        ]);
    }

    /**
     * Apply a specific configuration type to the router.
     */
    private function applyConfiguration(MikrotikRouter $router, string $configType, array $settings): bool
    {
        try {
            switch ($configType) {
                case 'pppoe':
                    return $this->configurePppoe($router, $settings);
                case 'ippool':
                    return $this->configureIpPool($router, $settings);
                case 'firewall':
                    return $this->configureFirewall($router, $settings);
                case 'queue':
                    return $this->configureQueue($router, $settings);
                default:
                    Log::channel('device_operations')->warning('Unknown configuration type', ['config_type' => $configType]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error applying configuration', [
                'router_id' => $router->id,
                'config_type' => $configType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Configure PPPoE server settings.
     */
    private function configurePppoe(MikrotikRouter $router, array $settings): bool
    {
        try {
            // Add or update PPPoE server configuration
            $pppoeConfig = [];

            if (array_key_exists('interface', $settings)) {
                $pppoeConfig['interface'] = $settings['interface'];
            }

            if (array_key_exists('service_name', $settings)) {
                $pppoeConfig['service-name'] = $settings['service_name'];
            }

            if (array_key_exists('default_profile', $settings)) {
                $pppoeConfig['default-profile'] = $settings['default_profile'];
            }

            if (empty($pppoeConfig)) {
                Log::channel('device_operations')->warning('No PPPoE configuration settings provided', [
                    'router_id' => $router->id,
                ]);

                return false;
            }

            // Check if PPPoE server already exists on the interface
            if (isset($pppoeConfig['interface'])) {
                $existing = $this->mikrotikApiService->getMktRows($router, '/interface/pppoe-server/server');
                foreach ($existing as $server) {
                    if (isset($server['interface']) && $server['interface'] === $pppoeConfig['interface']) {
                        Log::channel('device_operations')->info('PPPoE server already exists on interface, skipping creation', [
                            'router_id' => $router->id,
                            'interface' => $pppoeConfig['interface'],
                        ]);
                        return true;
                    }
                }
            }

            $result = $this->mikrotikApiService->addMktRows($router, '/interface/pppoe-server/server', [$pppoeConfig]);

            Log::channel('device_operations')->info('PPPoE configuration applied', [
                'router_id' => $router->id,
                'result' => $result,
            ]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error configuring PPPoE', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Configure IP pool settings.
     */
    private function configureIpPool(MikrotikRouter $router, array $settings): bool
    {
        try {
            // Add IP pool
            $poolConfig = [
                'name' => $settings['pool_name'] ?? 'default-pool',
                'ranges' => $settings['ip_range'] ?? '192.168.1.2-192.168.1.254',
            ];

            // Check for existing IP pool with the same name for this router to avoid duplicates
            $existingPool = MikrotikIpPool::where('router_id', $router->id)
                ->where('name', $poolConfig['name'])
                ->first();

            if ($existingPool !== null) {
                Log::channel('device_operations')->info('IP pool already exists, skipping creation', [
                    'router_id' => $router->id,
                    'pool_name' => $poolConfig['name'],
                ]);

                return true;
            }
            $result = $this->mikrotikApiService->addMktRows($router, '/ip/pool', [$poolConfig]);

            Log::channel('device_operations')->info('IP pool configuration applied', [
                'router_id' => $router->id,
                'result' => $result,
            ]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error configuring IP pool', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build and validate a firewall rule configuration.
     *
     * Returns a sanitized configuration array or null if the settings are invalid or unsafe.
     */
    private function buildValidatedFirewallConfig(array $settings): ?array
    {
        // Restrict chain and action to known-safe values
        $allowedChains = ['input', 'forward', 'output'];
        $allowedActions = ['accept', 'drop', 'reject', 'log'];

        $chain = $settings['chain'] ?? 'forward';
        $action = $settings['action'] ?? 'accept';

        if (!in_array($chain, $allowedChains, true)) {
            return null;
        }

        if (!in_array($action, $allowedActions, true)) {
            return null;
        }

        // Extract optional scoping parameters
        $srcAddress = $settings['src_address'] ?? null;
        $dstAddress = $settings['dst_address'] ?? null;
        $protocol = $settings['protocol'] ?? null;
        $srcPort = $settings['src_port'] ?? null;
        $dstPort = $settings['dst_port'] ?? null;

        // Require the rule to be scoped by address or by protocol+port
        $hasAddressScope = !empty($srcAddress) || !empty($dstAddress);
        $hasProtocolPortScope = !empty($protocol) && (!empty($srcPort) || !empty($dstPort));

        if (!$hasAddressScope && !$hasProtocolPortScope) {
            // Unsafe: overly broad rule with no meaningful scope
            return null;
        }

        // Build sanitized firewall configuration
        $firewallConfig = [
            'chain' => $chain,
            'action' => $action,
        ];

        if ($srcAddress !== null) {
            $firewallConfig['src-address'] = $srcAddress;
        }

        if ($dstAddress !== null) {
            $firewallConfig['dst-address'] = $dstAddress;
        }

        if ($protocol !== null) {
            $firewallConfig['protocol'] = $protocol;
        }

        if ($srcPort !== null) {
            $firewallConfig['src-port'] = $srcPort;
        }

        if ($dstPort !== null) {
            $firewallConfig['dst-port'] = $dstPort;
        }

        return $firewallConfig;
    }

    /**
     * Configure firewall rules.
     */
    private function configureFirewall(MikrotikRouter $router, array $settings): bool
    {
        try {
            // Build and validate firewall rule configuration
            $firewallConfig = $this->buildValidatedFirewallConfig($settings);
            if ($firewallConfig === null) {
                Log::channel('device_operations')->error('Firewall configuration rejected due to invalid or unsafe settings', [
                    'router_id' => $router->id,
                    'settings' => $settings,
                ]);
                return false;
            }

            $result = $this->mikrotikApiService->addMktRows($router, '/ip/firewall/filter', [$firewallConfig]);

            Log::channel('device_operations')->info('Firewall configuration applied', [
                'router_id' => $router->id,
                'result' => $result,
            ]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error configuring firewall', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Configure queue settings.
     */
    private function configureQueue(MikrotikRouter $router, array $settings): bool
    {
        try {
            // Add queue
            $queueConfig = [
                'name' => $settings['queue_name'] ?? 'default-queue',
                'max-limit' => $settings['max_limit'] ?? '10M/10M',
            ];

            $result = $this->mikrotikApiService->addMktRows($router, '/queue/simple', [$queueConfig]);

            Log::channel('device_operations')->info('Queue configuration applied', [
                'router_id' => $router->id,
                'result' => $result,
            ]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error configuring queue', [
                'router_id' => $router->id,
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $client = $this->createClient($router);
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $vpnData['username'])
                ->equal('password', $vpnData['password'])
                ->equal('service', $vpnData['service'] ?? 'l2tp')
                ->equal('profile', $vpnData['profile'] ?? 'default');

            $client->query($query)->read();

            MikrotikVpnAccount::create([
                'router_id' => $routerId,
                'username' => $vpnData['username'],
                'password' => $vpnData['password'],
                'profile' => $vpnData['profile'] ?? 'default',
                'enabled' => $vpnData['enabled'] ?? true,
            ]);

            Log::channel('device_operations')->info('VPN account created', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'username' => $vpnData['username'],
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while creating VPN account', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error creating VPN account', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $client = $this->createClient($router);
            $query = new Query('/ppp/active/print');
            $response = $client->query($query)->read();

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while getting VPN status', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting VPN status', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $client = $this->createClient($router);
            $query = (new Query('/queue/simple/add'))
                ->equal('name', $queueData['name'])
                ->equal('target', $queueData['target'])
                ->equal('parent', $queueData['parent'] ?? 'none')
                ->equal('max-limit', $queueData['max_limit'] ?? '')
                ->equal('burst-limit', $queueData['burst_limit'] ?? '')
                ->equal('burst-threshold', $queueData['burst_threshold'] ?? '')
                ->equal('burst-time', $queueData['burst_time'] ?? '0')
                ->equal('priority', $queueData['priority'] ?? '8');

            $client->query($query)->read();

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

            Log::channel('device_operations')->info('Queue created', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'queue' => $queueData['name'],
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while creating queue', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error creating queue', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $client = $this->createClient($router);
            $query = new Query('/queue/simple/print');
            $response = $client->query($query)->read();

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while getting queues', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting queues', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return false;
            }

            $client = $this->createClient($router);
            $query = new Query('/ip/firewall/filter/add');

            // Add all rule data as parameters, normalizing keys for MikroTik (underscores -> hyphens)
            foreach ($ruleData as $key => $value) {
                $normalizedKey = str_replace('_', '-', (string) $key);
                $query->equal($normalizedKey, $value);
            }

            $client->query($query)->read();

            Log::channel('device_operations')->info('Firewall rule added', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'chain' => $ruleData['chain'] ?? 'forward',
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while adding firewall rule', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error adding firewall rule', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            $client = $this->createClient($router);
            $query = new Query('/ip/firewall/filter/print');
            $response = $client->query($query)->read();

            return $this->normalizeApiResponse($response);
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while getting firewall rules', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting firewall rules', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
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
            Log::channel('device_operations')->warning('Invalid IP address format', ['ip' => $ipAddress]);

            return false;
        }

        // Block private IP ranges to prevent SSRF to internal network
        // Allow configuration override for legitimate internal routers
        $allowPrivateIps = config('services.mikrotik.allow_private_ips', false);

        if (! $allowPrivateIps) {
            // Check if IP is in private ranges
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                Log::channel('device_operations')->warning('Blocked connection to private/reserved IP address', [
                    'ip' => $ipAddress,
                    'hint' => 'Set services.mikrotik.allow_private_ips=true in config to allow internal IPs',
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Get system resources (CPU, memory, uptime) from MikroTik router
     *
     * Returns an array with keys: cpu-load, free-memory, total-memory, uptime
     *
     * {@inheritDoc}
     */
    public function getResources(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::channel('device_operations')->error('Router not found', ['router_id' => $routerId]);

                return [];
            }

            // Validate router IP to prevent SSRF attacks
            if (! $this->isValidRouterIpAddress($router->ip_address)) {
                Log::channel('device_operations')->error('Router IP address validation failed - potential SSRF attempt', [
                    'router_id' => $routerId,
                    'router_name' => $router->name,
                    'ip_address' => $router->ip_address,
                ]);

                return [];
            }

            // Get system resources from MikroTik via Binary API
            $startTime = microtime(true);
            $client = $this->createClient($router);
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();
            $responseTime = (int)((microtime(true) - $startTime) * 1000);

            Log::channel('device_operations')->info('System resources retrieved from MikroTik', [
                'router_id' => $routerId,
                'router_name' => $router->name,
            ]);

            // Update router status
            $router->update([
                'api_status' => 'online',
                'last_checked_at' => now(),
                'response_time_ms' => $responseTime,
                'last_error' => null,
            ]);

            return !empty($response) ? $response[0] : [];
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::channel('device_operations')->error('Socket connection timeout to MikroTik router while getting system resources', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            // Update router with error status
            if (isset($router)) {
                $router->update([
                    'api_status' => 'offline',
                    'last_checked_at' => now(),
                    'last_error' => 'Connection timeout: ' . $e->getMessage(),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::channel('device_operations')->error('Error getting system resources from MikroTik', [
                'router_id' => $routerId,
                'router_name' => $router->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            // Update router with error status
            if (isset($router)) {
                $router->update([
                    'api_status' => 'offline',
                    'last_checked_at' => now(),
                    'last_error' => $e->getMessage(),
                ]);
            }

            return [];
        }
    }

    /**
     * Create Binary API client for router with SSL/TLS support
     *
     * @param MikrotikRouter $router Router instance with encrypted credentials
     * @return Client Connected Binary API client
     */
    private function createClient(MikrotikRouter $router): Client
    {
        // Laravel automatically decrypts password via encrypted cast
        $config = (new Config())
            ->set('host', $router->ip_address)
            ->set('user', $router->username)
            ->set('pass', $router->password) // Already decrypted by Laravel
            ->set('port', $router->api_port)
            ->set('timeout', config('services.mikrotik.timeout', 60));

        // Enable SSL/TLS if configured (recommended for production)
        if (config('services.mikrotik.ssl', false)) {
            $config->set('ssl', true);
        }

        return new Client($config);
    }

    /**
     * Normalize Binary API response to match expected format
     *
     * @param array $response Raw response from Binary API
     * @return array Normalized response
     */
    private function normalizeApiResponse(array $response): array
    {
        return array_map(function ($item) {
            $normalized = [];

            foreach ($item as $key => $value) {
                // Keep original key
                $normalized[$key] = $value;

                // Skip dot-prefixed keys like .id for underscore conversion
                if (strpos($key, '.') === 0) {
                    continue;
                }

                // Convert hyphen to underscore for compatibility
                $underscoreKey = str_replace('-', '_', $key);
                if ($underscoreKey !== $key) {
                    $normalized[$underscoreKey] = $value;
                }
            }

            return $normalized;
        }, $response);
    }
}
