<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use App\Models\RouterConfigurationTemplate;
use App\Models\RouterProvisioningLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Router Provisioning Service
 *
 * Provides zero-touch router provisioning with automated configuration
 * for RADIUS, Hotspot, PPPoE, NAT, Firewall, and System settings.
 *
 * SECURITY NOTES:
 * 1. Communication: This service uses HTTP for router API communication, matching the existing
 *    MikrotikService implementation. For production environments with routers on untrusted networks,
 *    configure HTTPS with proper certificate validation. This is acceptable for routers on
 *    internal/trusted networks.
 * 2. Credentials: Router credentials are encrypted at rest in the database using Laravel's
 *    encrypted casting but are decrypted when transmitted to the router API.
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
            $response = Http::timeout(10)
                ->get("http://{$router->ip_address}:{$router->api_port}/health");

            if ($response->successful()) {
                // Check RouterOS version if available
                $data = $response->json();
                $version = $data['version'] ?? null;

                if ($version) {
                    Log::info('Router version detected', [
                        'router_id' => $routerId,
                        'version' => $version,
                    ]);
                }

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Connectivity check failed', [
                'router_id' => $routerId,
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
            $response = Http::timeout(30)
                ->get("http://{$router->ip_address}:{$router->api_port}/api/backup/export");

            if ($response->successful()) {
                RouterConfigurationBackup::create([
                    'router_id' => $routerId,
                    'created_by' => $userId,
                    'backup_data' => $response->body(),
                    'backup_type' => $backupType,
                    'created_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Configuration backup failed', [
                'router_id' => $routerId,
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
            $radiusConfig = [
                'address' => $config['server'] ?? '127.0.0.1',
                'secret' => $config['secret'] ?? '',
                'authentication-port' => $config['auth_port'] ?? 1812,
                'accounting-port' => $config['acct_port'] ?? 1813,
                'timeout' => $config['timeout'] ?? '3s',
                'service' => $config['service'] ?? 'ppp,hotspot',
            ];

            $response = Http::timeout(30)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/radius/add", $radiusConfig);

            if ($response->successful()) {
                Log::info('RADIUS configured', ['router_id' => $router->id]);

                return true;
            }

            Log::warning('RADIUS configuration failed', [
                'router_id' => $router->id,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('RADIUS configuration error', [
                'router_id' => $router->id,
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
            $hotspotConfig = [
                'name' => $config['profile_name'] ?? 'default',
                'hotspot-address' => $config['hotspot_address'] ?? '10.5.50.1',
                'dns-name' => $config['dns_name'] ?? 'hotspot.local',
                'login-by' => $config['login_by'] ?? 'mac,http-chap',
                'use-radius' => $config['use_radius'] ?? true,
                'mac-auth-mode' => $config['mac_auth_mode'] ?? 'mac-as-username',
                'cookie-timeout' => $config['cookie_timeout'] ?? '3d',
                'idle-timeout' => $config['idle_timeout'] ?? 'none',
                'keepalive-timeout' => $config['keepalive_timeout'] ?? '2m',
                'shared-users' => $config['shared_users'] ?? 1,
            ];

            $response = Http::timeout(30)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/hotspot/profile/add", $hotspotConfig);

            if ($response->successful()) {
                Log::info('Hotspot profile configured', ['router_id' => $router->id]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Hotspot configuration error', [
                'router_id' => $router->id,
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
            $pppoeConfig = [
                'service-name' => $config['service_name'] ?? 'pppoe',
                'interface' => $config['interface'] ?? 'ether2',
                'default-profile' => $config['default_profile'] ?? 'default',
                'authentication' => $config['authentication'] ?? 'pap,chap,mschap1,mschap2',
                'keepalive-timeout' => $config['keepalive_timeout'] ?? 10,
                'one-session-per-host' => $config['one_session_per_host'] ?? true,
                'max-sessions' => $config['max_sessions'] ?? 1000,
            ];

            // Configure PPPoE server
            $response = Http::timeout(30)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ppp/profile/add", $pppoeConfig);

            if (! $response->successful()) {
                return false;
            }

            // Configure IP pool for PPPoE
            if (isset($config['ip_pool'])) {
                $poolConfig = [
                    'name' => $config['ip_pool']['name'] ?? 'pppoe-pool',
                    'ranges' => $config['ip_pool']['ranges'] ?? '10.0.0.2-10.0.0.254',
                ];

                Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/pool/add", $poolConfig);
            }

            Log::info('PPPoE server configured', ['router_id' => $router->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('PPPoE configuration error', [
                'router_id' => $router->id,
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
            $rules = $config['rules'] ?? [];

            foreach ($rules as $rule) {
                $natRule = [
                    'chain' => $rule['chain'] ?? 'srcnat',
                    'action' => $rule['action'] ?? 'masquerade',
                    'src-address' => $rule['src_address'] ?? '',
                    'dst-address' => $rule['dst_address'] ?? '',
                    'out-interface' => $rule['out_interface'] ?? '',
                    'comment' => $rule['comment'] ?? '',
                ];

                $response = Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/nat/add", $natRule);

                if (! $response->successful()) {
                    Log::warning('NAT rule failed', [
                        'router_id' => $router->id,
                        'rule' => $rule,
                    ]);
                }
            }

            Log::info('NAT rules configured', ['router_id' => $router->id, 'count' => count($rules)]);

            return true;
        } catch (\Exception $e) {
            Log::error('NAT configuration error', [
                'router_id' => $router->id,
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
            $rules = $config['rules'] ?? [];

            foreach ($rules as $rule) {
                $firewallRule = [
                    'chain' => $rule['chain'] ?? 'forward',
                    'action' => $rule['action'] ?? 'accept',
                    'protocol' => $rule['protocol'] ?? '',
                    'src-address' => $rule['src_address'] ?? '',
                    'dst-address' => $rule['dst_address'] ?? '',
                    'dst-port' => $rule['dst_port'] ?? '',
                    'comment' => $rule['comment'] ?? '',
                ];

                $response = Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/filter/add", $firewallRule);

                if (! $response->successful()) {
                    Log::warning('Firewall rule failed', [
                        'router_id' => $router->id,
                        'rule' => $rule,
                    ]);
                }
            }

            Log::info('Firewall rules configured', ['router_id' => $router->id, 'count' => count($rules)]);

            return true;
        } catch (\Exception $e) {
            Log::error('Firewall configuration error', [
                'router_id' => $router->id,
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
            $entries = $config['entries'] ?? [];

            foreach ($entries as $entry) {
                $walledGardenEntry = [
                    'dst-host' => $entry['host'] ?? '',
                    'action' => $entry['action'] ?? 'allow',
                    'comment' => $entry['comment'] ?? '',
                ];

                $response = Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/hotspot/walled-garden/add", $walledGardenEntry);

                if (! $response->successful()) {
                    Log::warning('Walled garden entry failed', [
                        'router_id' => $router->id,
                        'entry' => $entry,
                    ]);
                }
            }

            // Add IP-based walled garden entries
            $ipEntries = $config['ip_entries'] ?? [];

            foreach ($ipEntries as $ipEntry) {
                $walledGardenIp = [
                    'dst-address' => $ipEntry['address'] ?? '',
                    'action' => $ipEntry['action'] ?? 'allow',
                    'comment' => $ipEntry['comment'] ?? '',
                ];

                Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/hotspot/walled-garden/ip/add", $walledGardenIp);
            }

            Log::info('Walled garden configured', ['router_id' => $router->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Walled garden configuration error', [
                'router_id' => $router->id,
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
            // Create IP pool for suspended users
            $poolConfig = [
                'name' => $config['pool_name'] ?? 'suspended-pool',
                'ranges' => $config['pool_range'] ?? '10.255.255.2-10.255.255.254',
            ];

            $response = Http::timeout(30)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/pool/add", $poolConfig);

            if (! $response->successful()) {
                return false;
            }

            // Add firewall rule to block suspended pool traffic
            $blockRule = [
                'chain' => 'forward',
                'action' => 'drop',
                'src-address' => $config['pool_network'] ?? '10.255.255.0/24',
                'comment' => 'Block suspended users',
            ];

            Http::timeout(30)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/filter/add", $blockRule);

            // Add redirect to payment/recharge page if configured
            if (isset($config['redirect_url'])) {
                $redirectRule = [
                    'chain' => 'dstnat',
                    'protocol' => 'tcp',
                    'dst-port' => '80,443',
                    'src-address' => $config['pool_network'] ?? '10.255.255.0/24',
                    'action' => 'redirect',
                    'to-ports' => '80',
                    'comment' => 'Redirect suspended users to payment page',
                ];

                Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/ip/firewall/nat/add", $redirectRule);
            }

            Log::info('Suspended users pool configured', ['router_id' => $router->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Suspended pool configuration error', [
                'router_id' => $router->id,
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
            // Set system identity
            if (isset($config['identity'])) {
                $response = Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/system/identity/set", [
                        'name' => $config['identity'],
                    ]);

                if (! $response->successful()) {
                    Log::warning('Failed to set system identity', ['router_id' => $router->id]);
                }
            }

            // Configure NTP client
            if (isset($config['ntp_servers'])) {
                foreach ($config['ntp_servers'] as $ntpServer) {
                    Http::timeout(30)
                        ->post("http://{$router->ip_address}:{$router->api_port}/api/system/ntp/client/set", [
                            'enabled' => true,
                            'primary-ntp' => $ntpServer,
                        ]);
                }
            }

            // Set timezone
            if (isset($config['timezone'])) {
                Http::timeout(30)
                    ->post("http://{$router->ip_address}:{$router->api_port}/api/system/clock/set", [
                        'time-zone-name' => $config['timezone'],
                    ]);
            }

            Log::info('System settings configured', ['router_id' => $router->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('System configuration error', [
                'router_id' => $router->id,
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
            $response = Http::timeout(10)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/radius/test", [
                    'address' => $radiusConfig['server'] ?? '127.0.0.1',
                    'secret' => $radiusConfig['secret'] ?? '',
                    'username' => 'test',
                    'password' => 'test',
                ]);

            return $response->successful();
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
            $response = Http::timeout(60)
                ->post("http://{$router->ip_address}:{$router->api_port}/api/backup/import", [
                    'data' => $backup->backup_data,
                ]);

            if ($response->successful()) {
                $log->update([
                    'status' => 'success',
                    'completed_at' => now(),
                ]);

                Log::info('Configuration rolled back', [
                    'router_id' => $routerId,
                    'backup_id' => $backupId,
                ]);

                return true;
            }

            $log->update([
                'status' => 'failed',
                'error_message' => 'Failed to restore backup',
                'completed_at' => now(),
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
}
