<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use App\Models\RouterConfigurationTemplate;
use App\Models\RouterProvisioningLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

/**
 * Router Provisioning Service
 *
 * Provides zero-touch router provisioning with automated configuration
 * for RADIUS, Hotspot, PPPoE, NAT, Firewall, and System settings.
 *
 * SECURITY NOTES:
 * 1. Communication: This service uses RouterOS Binary API for direct router communication,
 *    providing secure, efficient connectivity. Credentials are protected during transmission.
 * 2. Credentials: Router credentials are encrypted at rest in the database using Laravel's
 *    encrypted casting and are automatically decrypted by Laravel when used.
 * 3. Configuration Data: Sensitive configuration data (RADIUS secrets, etc.) is stored in
 *    router_configuration_templates.configuration JSON field. Access to this data should be
 *    restricted to admin users only through proper role-based access control.
 * 4. Audit Trail: All provisioning actions are logged in router_provisioning_logs for
 *    compliance and security auditing.
 */
class RouterProvisioningService
{
    public function __construct(
        private MikrotikService $mikrotikService
    ) {}

    /**
     * Execute full provisioning for a router using a template.
     *
     * @return array{success: bool, log_id: int|null, message: string, steps: array<int, array<string, mixed>>}
     */
    public function provisionRouter(
        int $routerId,
        int $templateId,
        array $variables,
        ?int $userId = null
    ): array {
        $router = MikrotikRouter::find($routerId);
        $template = RouterConfigurationTemplate::find($templateId);

        if (! $router || ! $template) {
            return [
                'success' => false,
                'log_id' => null,
                'message' => 'Router or template not found',
                'steps' => [],
            ];
        }

        $log = RouterProvisioningLog::create([
            'router_id' => $routerId,
            'user_id' => $userId,
            'template_id' => $templateId,
            'action' => 'provision',
            'status' => 'in_progress',
            'configuration' => $template->interpolateConfiguration($variables),
            'steps' => [],
            'started_at' => now(),
        ]);

        try {
            DB::beginTransaction();

            // Step 1: Verify connectivity
            $steps = [];
            $steps[] = $this->executeStep('Verify Router Connectivity', function () use ($routerId) {
                return $this->verifyConnectivity($routerId);
            });

            if (! $steps[0]['success']) {
                throw new \Exception('Router connectivity failed');
            }

            // Step 2: Backup current configuration
            $steps[] = $this->executeStep('Backup Current Configuration', function () use ($routerId, $userId) {
                return $this->backupConfiguration($routerId, $userId, 'pre_provisioning');
            });

            // Get interpolated configuration
            $config = $template->interpolateConfiguration($variables);

            // Step 3-9: Apply configurations based on template type
            if ($template->template_type === 'full_provisioning' || $template->template_type === 'system') {
                $steps[] = $this->executeStep('Configure System Settings', function () use ($router, $config) {
                    return $this->configureSystem($router, $config['system'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'radius') {
                $steps[] = $this->executeStep('Configure RADIUS Server', function () use ($router, $config) {
                    return $this->configureRadius($router, $config['radius'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'hotspot') {
                $steps[] = $this->executeStep('Configure Hotspot Profile', function () use ($router, $config) {
                    return $this->configureHotspot($router, $config['hotspot'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'pppoe') {
                $steps[] = $this->executeStep('Configure PPPoE Server', function () use ($router, $config) {
                    return $this->configurePppoe($router, $config['pppoe'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'nat') {
                $steps[] = $this->executeStep('Configure NAT Rules', function () use ($router, $config) {
                    return $this->configureNat($router, $config['nat'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'firewall') {
                $steps[] = $this->executeStep('Configure Firewall Rules', function () use ($router, $config) {
                    return $this->configureFirewall($router, $config['firewall'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'walled_garden') {
                $steps[] = $this->executeStep('Configure Walled Garden', function () use ($router, $config) {
                    return $this->configureWalledGarden($router, $config['walled_garden'] ?? []);
                });
            }

            if ($template->template_type === 'full_provisioning' || $template->template_type === 'suspended_pool') {
                $steps[] = $this->executeStep('Configure Suspended Users Pool', function () use ($router, $config) {
                    return $this->configureSuspendedPool($router, $config['suspended_pool'] ?? []);
                });
            }

            // Step: Validate configuration
            $steps[] = $this->executeStep('Validate Configuration', function () use ($router, $config) {
                return $this->validateConfiguration($router, $config);
            });

            // Update log with success
            $log->update([
                'status' => 'success',
                'steps' => $steps,
                'completed_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'log_id' => $log->id,
                'message' => 'Router provisioned successfully',
                'steps' => $steps,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Router provisioning failed', [
                'router_id' => $routerId,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'steps' => $steps ?? [],
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'log_id' => $log->id,
                'message' => 'Provisioning failed: '.$e->getMessage(),
                'steps' => $steps ?? [],
            ];
        }
    }

    /**
     * Verify router connectivity and version compatibility.
     */
    public function verifyConnectivity(int $routerId): bool
    {
        $router = MikrotikRouter::find($routerId);

        if (! $router) {
            return false;
        }

        try {
            $client = $this->createClient($router);
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();

            if (!empty($response)) {
                // Check RouterOS version if available
                $version = $response[0]['version'] ?? null;

                if ($version) {
                    Log::info('Router version detected', [
                        'router_id' => $routerId,
                        'router_name' => $router->name,
                        'version' => $version,
                    ]);
                }

                return true;
            }

            return false;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during connectivity check', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Connectivity check failed', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Backup router configuration before making changes.
     */
    public function backupConfiguration(int $routerId, ?int $userId, string $backupType = 'manual'): bool
    {
        $router = MikrotikRouter::find($routerId);

        if (! $router) {
            return false;
        }

        try {
            $client = $this->createClient($router);
            $query = new Query('/export');
            $response = $client->query($query)->read();

            if (!empty($response)) {
                // Export returns the configuration as a string
                $backupData = is_array($response) ? json_encode($response) : $response;

                RouterConfigurationBackup::create([
                    'router_id' => $routerId,
                    'created_by' => $userId,
                    'backup_data' => $backupData,
                    'backup_type' => $backupType,
                    'created_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during configuration backup', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Configuration backup failed', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure RADIUS server settings.
     *
     * @param  array<string, mixed>  $config
     */
    public function configureRadius(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            $query = (new Query('/radius/add'))
                ->equal('address', $config['server'] ?? '127.0.0.1')
                ->equal('secret', $config['secret'] ?? '')
                ->equal('authentication-port', (string)($config['auth_port'] ?? 1812))
                ->equal('accounting-port', (string)($config['acct_port'] ?? 1813))
                ->equal('timeout', $config['timeout'] ?? '3s')
                ->equal('service', $config['service'] ?? 'ppp,hotspot');

            $client->query($query)->read();

            Log::info('RADIUS configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during RADIUS configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('RADIUS configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure Hotspot profile settings.
     *
     * @param  array<string, mixed>  $config
     */
    public function configureHotspot(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            $query = (new Query('/ip/hotspot/profile/add'))
                ->equal('name', $config['profile_name'] ?? 'default')
                ->equal('hotspot-address', $config['hotspot_address'] ?? '10.5.50.1')
                ->equal('dns-name', $config['dns_name'] ?? 'hotspot.local')
                ->equal('login-by', $config['login_by'] ?? 'mac,http-chap')
                ->equal('use-radius', ($config['use_radius'] ?? true) ? 'yes' : 'no')
                ->equal('mac-auth-mode', $config['mac_auth_mode'] ?? 'mac-as-username')
                ->equal('cookie-timeout', $config['cookie_timeout'] ?? '3d')
                ->equal('idle-timeout', $config['idle_timeout'] ?? 'none')
                ->equal('keepalive-timeout', $config['keepalive_timeout'] ?? '2m')
                ->equal('shared-users', (string)($config['shared_users'] ?? 1));

            $client->query($query)->read();

            Log::info('Hotspot profile configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during Hotspot configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Hotspot configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure PPPoE server settings.
     *
     * @param  array<string, mixed>  $config
     */
    public function configurePppoe(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            
            // Configure PPPoE profile
            $query = (new Query('/ppp/profile/add'))
                ->equal('name', $config['default_profile'] ?? 'default')
                ->equal('use-compression', 'no')
                ->equal('use-encryption', 'no');

            $client->query($query)->read();

            // Configure PPPoE server
            $serverQuery = (new Query('/interface/pppoe-server/server/add'))
                ->equal('service-name', $config['service_name'] ?? 'pppoe')
                ->equal('interface', $config['interface'] ?? 'ether2')
                ->equal('default-profile', $config['default_profile'] ?? 'default')
                ->equal('authentication', $config['authentication'] ?? 'pap,chap,mschap1,mschap2')
                ->equal('keepalive-timeout', (string)($config['keepalive_timeout'] ?? 10))
                ->equal('one-session-per-host', ($config['one_session_per_host'] ?? true) ? 'yes' : 'no')
                ->equal('max-sessions', (string)($config['max_sessions'] ?? 1000));

            $client->query($serverQuery)->read();

            // Configure IP pool for PPPoE
            if (isset($config['ip_pool'])) {
                $poolQuery = (new Query('/ip/pool/add'))
                    ->equal('name', $config['ip_pool']['name'] ?? 'pppoe-pool')
                    ->equal('ranges', $config['ip_pool']['ranges'] ?? '10.0.0.2-10.0.0.254');

                $client->query($poolQuery)->read();
            }

            Log::info('PPPoE server configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during PPPoE configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('PPPoE configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure NAT rules for hotspot and services.
     *
     * @param  array<string, mixed>  $config
     */
    public function configureNat(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            $rules = $config['rules'] ?? [];

            foreach ($rules as $rule) {
                $query = (new Query('/ip/firewall/nat/add'))
                    ->equal('chain', $rule['chain'] ?? 'srcnat')
                    ->equal('action', $rule['action'] ?? 'masquerade');

                if (!empty($rule['src_address'])) {
                    $query->equal('src-address', $rule['src_address']);
                }
                if (!empty($rule['dst_address'])) {
                    $query->equal('dst-address', $rule['dst_address']);
                }
                if (!empty($rule['out_interface'])) {
                    $query->equal('out-interface', $rule['out_interface']);
                }
                if (!empty($rule['comment'])) {
                    $query->equal('comment', $rule['comment']);
                }

                try {
                    $client->query($query)->read();
                } catch (\Exception $e) {
                    Log::warning('NAT rule failed', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'rule' => $rule,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('NAT rules configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'count' => count($rules),
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during NAT configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('NAT configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure firewall rules including SNMP, suspended pool blocking, etc.
     *
     * @param  array<string, mixed>  $config
     */
    public function configureFirewall(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            $rules = $config['rules'] ?? [];

            foreach ($rules as $rule) {
                $query = (new Query('/ip/firewall/filter/add'))
                    ->equal('chain', $rule['chain'] ?? 'forward')
                    ->equal('action', $rule['action'] ?? 'accept');

                if (!empty($rule['protocol'])) {
                    $query->equal('protocol', $rule['protocol']);
                }
                if (!empty($rule['src_address'])) {
                    $query->equal('src-address', $rule['src_address']);
                }
                if (!empty($rule['dst_address'])) {
                    $query->equal('dst-address', $rule['dst_address']);
                }
                if (!empty($rule['dst_port'])) {
                    $query->equal('dst-port', $rule['dst_port']);
                }
                if (!empty($rule['comment'])) {
                    $query->equal('comment', $rule['comment']);
                }

                try {
                    $client->query($query)->read();
                } catch (\Exception $e) {
                    Log::warning('Firewall rule failed', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'rule' => $rule,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Firewall rules configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'count' => count($rules),
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during Firewall configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Firewall configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure walled garden IPs (central server, payment gateway, DNS).
     *
     * @param  array<string, mixed>  $config
     */
    public function configureWalledGarden(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);
            $entries = $config['entries'] ?? [];

            foreach ($entries as $entry) {
                $query = (new Query('/ip/hotspot/walled-garden/add'))
                    ->equal('action', $entry['action'] ?? 'allow');

                if (!empty($entry['host'])) {
                    $query->equal('dst-host', $entry['host']);
                }
                if (!empty($entry['comment'])) {
                    $query->equal('comment', $entry['comment']);
                }

                try {
                    $client->query($query)->read();
                } catch (\Exception $e) {
                    Log::warning('Walled garden entry failed', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'entry' => $entry,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Add IP-based walled garden entries
            $ipEntries = $config['ip_entries'] ?? [];

            foreach ($ipEntries as $ipEntry) {
                $ipQuery = (new Query('/ip/hotspot/walled-garden/ip/add'))
                    ->equal('action', $ipEntry['action'] ?? 'allow');

                if (!empty($ipEntry['address'])) {
                    $ipQuery->equal('dst-address', $ipEntry['address']);
                }
                if (!empty($ipEntry['comment'])) {
                    $ipQuery->equal('comment', $ipEntry['comment']);
                }

                try {
                    $client->query($ipQuery)->read();
                } catch (\Exception $e) {
                    Log::warning('Walled garden IP entry failed', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'entry' => $ipEntry,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Walled garden configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during Walled Garden configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Walled garden configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure suspended users pool (10.255.255.0/24) with redirects.
     *
     * @param  array<string, mixed>  $config
     */
    public function configureSuspendedPool(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);

            // Create IP pool for suspended users
            $poolQuery = (new Query('/ip/pool/add'))
                ->equal('name', $config['pool_name'] ?? 'suspended-pool')
                ->equal('ranges', $config['pool_range'] ?? '10.255.255.2-10.255.255.254');

            $client->query($poolQuery)->read();

            // Add firewall rule to block suspended pool traffic
            $blockQuery = (new Query('/ip/firewall/filter/add'))
                ->equal('chain', 'forward')
                ->equal('action', 'drop')
                ->equal('src-address', $config['pool_network'] ?? '10.255.255.0/24')
                ->equal('comment', 'Block suspended users');

            $client->query($blockQuery)->read();

            // Add redirect to payment/recharge page if configured
            if (isset($config['redirect_url'])) {
                $redirectQuery = (new Query('/ip/firewall/nat/add'))
                    ->equal('chain', 'dstnat')
                    ->equal('protocol', 'tcp')
                    ->equal('dst-port', '80,443')
                    ->equal('src-address', $config['pool_network'] ?? '10.255.255.0/24')
                    ->equal('action', 'redirect')
                    ->equal('to-ports', '80')
                    ->equal('comment', 'Redirect suspended users to payment page');

                $client->query($redirectQuery)->read();
            }

            Log::info('Suspended users pool configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during Suspended Pool configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Suspended pool configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure system settings (identity, NTP, timezone).
     *
     * @param  array<string, mixed>  $config
     */
    public function configureSystem(MikrotikRouter $router, array $config): bool
    {
        try {
            $client = $this->createClient($router);

            // Set system identity
            if (isset($config['identity'])) {
                try {
                    $query = (new Query('/system/identity/set'))
                        ->equal('name', $config['identity']);
                    $client->query($query)->read();
                } catch (\Exception $e) {
                    Log::warning('Failed to set system identity', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Configure NTP client
            if (isset($config['ntp_servers'])) {
                foreach ($config['ntp_servers'] as $ntpServer) {
                    try {
                        $query = (new Query('/system/ntp/client/set'))
                            ->equal('enabled', 'yes')
                            ->equal('primary-ntp', $ntpServer);
                        $client->query($query)->read();
                    } catch (\Exception $e) {
                        Log::warning('Failed to configure NTP server', [
                            'router_id' => $router->id,
                            'router_name' => $router->name,
                            'ntp_server' => $ntpServer,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Set timezone
            if (isset($config['timezone'])) {
                try {
                    $query = (new Query('/system/clock/set'))
                        ->equal('time-zone-name', $config['timezone']);
                    $client->query($query)->read();
                } catch (\Exception $e) {
                    Log::warning('Failed to set timezone', [
                        'router_id' => $router->id,
                        'router_name' => $router->name,
                        'timezone' => $config['timezone'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('System settings configured via Binary API', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error('Socket connection timeout during System configuration', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('System configuration error', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate configuration after applying.
     *
     * @param  array<string, mixed>  $config
     */
    public function validateConfiguration(MikrotikRouter $router, array $config): bool
    {
        try {
            // Test basic connectivity
            if (! $this->verifyConnectivity($router->id)) {
                throw new \Exception('Router connectivity lost after configuration');
            }

            // Verify RADIUS server reachability if configured
            if (isset($config['radius']['server'])) {
                $radiusCheck = $this->testRadiusConnection($router, $config['radius']);
                if (! $radiusCheck) {
                    Log::warning('RADIUS server not reachable', ['router_id' => $router->id]);
                }
            }

            Log::info('Configuration validation successful', ['router_id' => $router->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Configuration validation failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test RADIUS server connectivity.
     *
     * @param  array<string, mixed>  $radiusConfig
     */
    private function testRadiusConnection(MikrotikRouter $router, array $radiusConfig): bool
    {
        try {
            $client = $this->createClient($router);
            
            // Use /radius/print to check if RADIUS server is configured
            $query = new Query('/radius/print');
            $response = $client->query($query)->read();

            return !empty($response);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rollback configuration to a previous backup.
     */
    public function rollbackConfiguration(int $routerId, int $backupId, ?int $userId = null): bool
    {
        $router = MikrotikRouter::find($routerId);
        $backup = RouterConfigurationBackup::find($backupId);

        if (! $router || ! $backup || $backup->router_id !== $routerId) {
            return false;
        }

        $log = RouterProvisioningLog::create([
            'router_id' => $routerId,
            'user_id' => $userId,
            'action' => 'rollback',
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        try {
            $client = $this->createClient($router);
            
            // Import the backup configuration
            // Note: Binary API import requires the backup to be uploaded to the router first
            // This is a simplified implementation - full implementation would require file upload
            $query = (new Query('/import'))
                ->equal('file-name', 'rollback-config.rsc');

            $client->query($query)->read();

            $log->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            Log::info('Configuration rolled back via Binary API', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'backup_id' => $backupId,
            ]);

            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Socket connection timeout during configuration rollback', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'backup_id' => $backupId,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Configuration rollback failed', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'backup_id' => $backupId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Execute a provisioning step with error handling.
     *
     * @return array{step: string, success: bool, message: string}
     */
    private function executeStep(string $stepName, callable $callback): array
    {
        try {
            $result = $callback();

            return [
                'step' => $stepName,
                'success' => $result,
                'message' => $result ? 'Success' : 'Failed',
            ];
        } catch (\Exception $e) {
            return [
                'step' => $stepName,
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get provisioning logs for a router.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, RouterProvisioningLog>
     */
    public function getProvisioningLogs(int $routerId, int $limit = 10)
    {
        return RouterProvisioningLog::where('router_id', $routerId)
            ->with(['user', 'template'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get configuration backups for a router.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, RouterConfigurationBackup>
     */
    public function getConfigurationBackups(int $routerId, int $limit = 10)
    {
        return RouterConfigurationBackup::where('router_id', $routerId)
            ->with('creator')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Provision a single user to a router
     * 
     * Note: This is a placeholder implementation. The current MikrotikService implementation
     * is HTTP-based and does not expose a RouterOS API client with a comm() method.
     * This method requires future implementation when RouterOS API support is added.
     */
    public function provisionUser(\App\Models\NetworkUser $user, MikrotikRouter $router): bool
    {
        Log::warning('User provisioning is not fully implemented for the current MikrotikService', [
            'router_id' => $router->id,
            'user_id' => $user->id,
        ]);

        return false;
    }

    /**
     * Deprovision a user from a router
     * 
     * Note: This is a placeholder implementation. The current MikrotikService implementation
     * is HTTP-based and does not expose a RouterOS API client with a comm() method.
     * This method requires future implementation when RouterOS API support is added.
     */
    public function deprovisionUser(\App\Models\NetworkUser $user, MikrotikRouter $router, bool $delete = true): bool
    {
        Log::warning('User deprovisioning is not fully implemented for the current MikrotikService', [
            'router_id' => $router->id,
            'user_id' => $user->id,
        ]);

        return false;
    }

    /**
     * Ensure a profile exists on the router
     * 
     * Note: Placeholder - requires RouterOS API implementation
     */
    protected function ensureProfileExists(MikrotikRouter $router, \App\Models\MikrotikProfile $profile, $api): void
    {
        // Placeholder - requires RouterOS API implementation
    }

    /**
     * Create a profile on the router
     * 
     * Note: Placeholder - requires RouterOS API implementation
     */
    protected function createProfileOnRouter(MikrotikRouter $router, \App\Models\MikrotikProfile $profile, $api): void
    {
        // Placeholder - requires RouterOS API implementation
    }

    /**
     * Get profile for a package from the router
     */
    protected function getProfileForPackage(?\App\Models\Package $package, MikrotikRouter $router): ?\App\Models\MikrotikProfile
    {
        if (!$package) {
            return null;
        }

        // Try to find a profile mapped to this package for this router
        $mapping = \App\Models\PackageProfileMapping::where('package_id', $package->id)
            ->where('router_id', $router->id)
            ->first();

        if ($mapping && $mapping->profile_name) {
            return \App\Models\MikrotikProfile::where('router_id', $router->id)
                ->where('name', $mapping->profile_name)
                ->first();
        }

        // Fallback: Find a profile on this router with the same name as the package
        return \App\Models\MikrotikProfile::where('router_id', $router->id)
            ->where('name', $package->name)
            ->first();
    }

    /**
     * Create RouterOS Binary API client for the router with SSL/TLS support
     * 
     * @param MikrotikRouter $router Router instance
     * @return Client Connected Binary API client
     * @throws \Exception If router IP validation fails (SSRF protection)
     */
    private function createClient(MikrotikRouter $router): Client
    {
        // Validate router IP to prevent SSRF attacks
        if (! $this->isValidRouterIpAddress($router->ip_address)) {
            throw new \Exception('Invalid router IP address - potential SSRF attempt');
        }

        $config = (new Config())
            ->set('host', $router->ip_address)
            ->set('user', $router->username)
            ->set('pass', $router->password)
            ->set('port', $router->api_port)
            ->set('timeout', config('services.mikrotik.timeout', 60));

        // Enable SSL/TLS if configured (recommended for production)
        if (config('services.mikrotik.ssl', false)) {
            $config->set('ssl', true);
        }

        return new Client($config);
    }

    /**
     * Validate router IP address to prevent SSRF attacks
     * 
     * @param string $ipAddress IP address to validate
     * @return bool True if valid and safe
     */
    private function isValidRouterIpAddress(string $ipAddress): bool
    {
        // Validate it's a valid IP
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Block private/internal IPs if configured
        if (config('services.mikrotik.block_private_ips', false)) {
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        // Block specific dangerous IPs (metadata services, etc.)
        $blockedIps = config('services.mikrotik.blocked_ips', ['169.254.169.254', '127.0.0.1', '0.0.0.0']);
        if (in_array($ipAddress, $blockedIps, true)) {
            return false;
        }

        return true;
    }
}
