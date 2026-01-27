<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MikrotikImportService
{
    protected MikrotikService $mikrotikService;

    protected MikrotikApiService $mikrotikApiService;

    public function __construct(MikrotikService $mikrotikService, MikrotikApiService $mikrotikApiService)
    {
        $this->mikrotikService = $mikrotikService;
        $this->mikrotikApiService = $mikrotikApiService;
    }

    /**
     * Import IP pools in bulk with CIDR/range support.
     *
     * Supported formats:
     * - CIDR notation: 192.168.1.0/24
     * - Hyphen ranges: 192.168.1.1-254
     * - Comma-separated: 192.168.1.1,192.168.1.2,192.168.1.3
     */
    public function importIpPools(array $data): array
    {
        $tenantId = auth()->user()->tenant_id;
        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            // Create CSV backup before import
            $this->backupIpPools($tenantId);

            foreach ($data['pools'] as $poolData) {
                try {
                    $ipList = $this->parseIpRange($poolData['ip_range']);

                    foreach ($ipList as $ip) {
                        IpPool::create([
                            'name' => $poolData['name'] ?? "Pool-{$ip}",
                            'ip_address' => $ip,
                            'subnet_mask' => $poolData['subnet_mask'] ?? '255.255.255.0',
                            'gateway' => $poolData['gateway'] ?? null,
                            'pool_type' => $poolData['pool_type'] ?? 'pppoe',
                            'tenant_id' => $tenantId,
                            'nas_id' => $poolData['nas_id'] ?? null,
                            'status' => 'available',
                        ]);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Failed to import pool {$poolData['name']}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import IP pools', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => 0,
                'failed' => count($data['pools']),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Parse IP range in various formats to array of IPs.
     */
    private function parseIpRange(string $range): array
    {
        $ips = [];

        // Handle CIDR notation (e.g., 192.168.1.0/24)
        if (str_contains($range, '/')) {
            $ips = $this->parseCidr($range);
        }
        // Handle hyphen range (e.g., 192.168.1.1-254)
        elseif (str_contains($range, '-')) {
            $ips = $this->parseHyphenRange($range);
        }
        // Handle comma-separated (e.g., 192.168.1.1,192.168.1.2)
        elseif (str_contains($range, ',')) {
            $ips = array_map('trim', explode(',', $range));
        }
        // Single IP
        else {
            $ips = [$range];
        }

        return $ips;
    }

    /**
     * Parse CIDR notation to IP list.
     */
    private function parseCidr(string $cidr): array
    {
        [$ip, $prefix] = explode('/', $cidr);
        $ips = [];

        // Convert IP to long
        $ipLong = ip2long($ip);
        $netmask = ~((1 << (32 - $prefix)) - 1);
        $network = $ipLong & $netmask;
        $broadcast = $network | ~$netmask;

        // Generate IPs (skip network and broadcast addresses)
        for ($i = $network + 1; $i < $broadcast; $i++) {
            $ips[] = long2ip($i);
        }

        return $ips;
    }

    /**
     * Parse hyphen range to IP list.
     */
    private function parseHyphenRange(string $range): array
    {
        preg_match('/^(\d+\.\d+\.\d+\.)(\d+)-(\d+)$/', $range, $matches);

        if (count($matches) !== 4) {
            throw new \InvalidArgumentException("Invalid IP range format: {$range}");
        }

        $prefix = $matches[1];
        $start = (int) $matches[2];
        $end = (int) $matches[3];

        $ips = [];
        for ($i = $start; $i <= $end; $i++) {
            $ips[] = $prefix . $i;
        }

        return $ips;
    }

    /**
     * Import PPP profiles from router.
     */
    public function importPppProfiles(int $routerId): array
    {
        $tenantId = auth()->user()->tenant_id;

        try {
            // Connect to router
            if (! $this->mikrotikService->connectRouter($routerId)) {
                throw new \Exception('Failed to connect to router');
            }

            // Create backup
            $this->backupPppProfiles($tenantId);

            // Fetch profiles from router via RouterOS API
            // Note: This is a simplified version. In production, use actual RouterOS API client
            $profiles = $this->fetchPppProfilesFromRouter($routerId);

            $imported = 0;
            $failed = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($profiles as $profileData) {
                try {
                    MikrotikProfile::updateOrCreate(
                        [
                            'name' => $profileData['name'],
                            'router_id' => $routerId,
                        ],
                        [
                            'local_address' => $profileData['local_address'] ?? null,
                            'remote_address' => $profileData['remote_address'] ?? null,
                            'rate_limit' => $profileData['rate_limit'] ?? null,
                            'tenant_id' => $tenantId,
                        ]
                    );
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Failed to import profile {$profileData['name']}: {$e->getMessage()}";
                }
            }
            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import PPP profiles', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => 0,
                'failed' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Import PPP secrets (customers) from router.
     */
    public function importPppSecrets(int $routerId, array $options = [], ?int $tenantId = null, ?int $userId = null): array
    {
        // Use provided tenant_id or fall back to auth user (for web requests)
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        $userId = $userId ?? auth()->id();
        $filterDisabled = $options['filter_disabled'] ?? true;
        $generateBills = $options['generate_bills'] ?? false;

        try {
            // Connect to router
            if (! $this->mikrotikService->connectRouter($routerId)) {
                throw new \Exception('Failed to connect to router');
            }

            // Create backup
            $this->backupCustomers($tenantId);

            // Fetch secrets from router
            $secrets = $this->fetchPppSecretsFromRouter($routerId, $filterDisabled);

            $imported = 0;
            $failed = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($secrets as $secretData) {
                try {
                    // Create user account if not exists
                    $user = User::firstOrCreate(
                        [
                            'mobile' => $secretData['mobile'] ?? $secretData['username'],
                            'tenant_id' => $tenantId,
                        ],
                        [
                            'name' => $secretData['name'] ?? $secretData['username'],
                            'email' => $secretData['email'] ?? null,
                            'password' => bcrypt($secretData['password']),
                            'role_id' => $this->getCustomerRoleId(),
                            'is_active' => true,
                        ]
                    );

                    // Create network user
                    NetworkUser::create([
                        'username' => $secretData['username'],
                        'password' => $secretData['password'],
                        'user_id' => $user->id,
                        'tenant_id' => $tenantId,
                        'service_type' => 'pppoe',
                        'package_id' => $secretData['package_id'] ?? null,
                        'status' => $secretData['disabled'] ? 'inactive' : 'active',
                        'is_active' => ! $secretData['disabled'],
                    ]);

                    // Generate initial bill if requested
                    if ($generateBills && ! $secretData['disabled']) {
                        // This would call BillingService to generate bill
                        // Skipped for brevity
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Failed to import customer {$secretData['username']}: {$e->getMessage()}";
                }
            }
            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import PPP secrets', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => 0,
                'failed' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Create CSV backup of IP pools.
     */
    private function backupIpPools(int $tenantId): void
    {
        $pools = IpPool::where('tenant_id', $tenantId)->get();
        $csv = "id,name,ip_address,subnet_mask,gateway,pool_type,status,created_at\n";

        foreach ($pools as $pool) {
            $csv .= "{$pool->id},{$pool->name},{$pool->ip_address},{$pool->subnet_mask},{$pool->gateway},{$pool->pool_type},{$pool->status},{$pool->created_at}\n";
        }

        $filename = 'ip_pools_backup_' . date('Y-m-d_His') . '.csv';
        Storage::disk('local')->put("imports/backups/{$filename}", $csv);
    }

    /**
     * Create CSV backup of PPP profiles.
     */
    private function backupPppProfiles(int $tenantId): void
    {
        $profiles = MikrotikProfile::where('tenant_id', $tenantId)->get();
        $csv = "id,name,router_id,local_address,remote_address,rate_limit,created_at\n";

        foreach ($profiles as $profile) {
            $csv .= "{$profile->id},{$profile->name},{$profile->router_id},{$profile->local_address},{$profile->remote_address},{$profile->rate_limit},{$profile->created_at}\n";
        }

        $filename = 'ppp_profiles_backup_' . date('Y-m-d_His') . '.csv';
        Storage::disk('local')->put("imports/backups/{$filename}", $csv);
    }

    /**
     * Create CSV backup of customers.
     */
    private function backupCustomers(int $tenantId): void
    {
        $customers = NetworkUser::where('tenant_id', $tenantId)->get();
        $csv = "id,username,user_id,service_type,package_id,status,is_active,created_at\n";

        foreach ($customers as $customer) {
            $csv .= "{$customer->id},{$customer->username},{$customer->user_id},{$customer->service_type},{$customer->package_id},{$customer->status},{$customer->is_active},{$customer->created_at}\n";
        }

        $filename = 'customers_backup_' . date('Y-m-d_His') . '.csv';
        Storage::disk('local')->put("imports/backups/{$filename}", $csv);
    }

    /**
     * Fetch PPP profiles from router via API.
     */
    private function fetchPppProfilesFromRouter(int $routerId): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found for fetching profiles', ['router_id' => $routerId]);

                return [];
            }

            // Fetch profiles from router using API service
            $profiles = $this->mikrotikApiService->getMktRows($router, '/ppp/profile');

            // Normalize profiles to expected format
            return array_map(function ($profile) {
                return [
                    'name' => $profile['name'] ?? '',
                    'local_address' => $profile['local-address'] ?? '',
                    'remote_address' => $profile['remote-address'] ?? '',
                    'rate_limit' => $profile['rate-limit'] ?? '',
                    'session_timeout' => $profile['session-timeout'] ?? '',
                    'idle_timeout' => $profile['idle-timeout'] ?? '',
                    'only_one' => isset($profile['only-one']) ? ($profile['only-one'] === 'yes') : false,
                    'change_tcp_mss' => isset($profile['change-tcp-mss']) ? ($profile['change-tcp-mss'] === 'yes') : true,
                ];
            }, $profiles);
        } catch (\Exception $e) {
            Log::error('Error fetching PPP profiles from router', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch PPP secrets from router via API.
     */
    private function fetchPppSecretsFromRouter(int $routerId, bool $filterDisabled): array
    {
        try {
            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found for fetching secrets', ['router_id' => $routerId]);

                return [];
            }

            // Fetch secrets from router using API service
            $secrets = $this->mikrotikApiService->getMktRows($router, '/ppp/secret');

            // Filter disabled secrets if requested
            if ($filterDisabled) {
                $secrets = array_filter($secrets, function ($secret) {
                    return ! isset($secret['disabled']) || $secret['disabled'] !== 'yes';
                });
            }

            // Normalize secrets to expected format
            return array_map(function ($secret) {
                return [
                    'name' => $secret['name'] ?? '',
                    'password' => $secret['password'] ?? '',
                    'service' => $secret['service'] ?? 'pppoe',
                    'profile' => $secret['profile'] ?? 'default',
                    'local_address' => $secret['local-address'] ?? '',
                    'remote_address' => $secret['remote-address'] ?? '',
                    'comment' => $secret['comment'] ?? '',
                    'disabled' => isset($secret['disabled']) ? ($secret['disabled'] === 'yes') : false,
                ];
            }, $secrets);
        } catch (\Exception $e) {
            Log::error('Error fetching PPP secrets from router', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get customer role ID.
     */
    private function getCustomerRoleId(): int
    {
        return DB::table('roles')->where('slug', 'customer')->value('id') ?? 1;
    }
}
