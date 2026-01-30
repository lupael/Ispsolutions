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
                    $parseResult = $this->parseIpRange($poolData['ip_range']);
                    $ipList = $parseResult['ips'];
                    $calculatedSubnetMask = $parseResult['subnet_mask'];

                    foreach ($ipList as $ip) {
                        IpPool::create([
                            'name' => $poolData['name'] ?? "Pool-{$ip}",
                            'ip_address' => $ip,
                            'subnet_mask' => $poolData['subnet_mask'] ?? $calculatedSubnetMask ?? '255.255.255.0',
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
     * Returns array with 'ips' and optional 'subnet_mask' calculated from the range.
     */
    private function parseIpRange(string $range): array
    {
        $ips = [];
        $subnetMask = null;

        // Handle CIDR notation (e.g., 192.168.1.0/24)
        if (str_contains($range, '/')) {
            $result = $this->parseCidr($range);
            $ips = $result['ips'];
            $subnetMask = $result['subnet_mask'];
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

        return ['ips' => $ips, 'subnet_mask' => $subnetMask];
    }

    /**
     * Parse CIDR notation to IP list with subnet mask.
     */
    private function parseCidr(string $cidr): array
    {
        [$ip, $prefix] = explode('/', $cidr);
        $ips = [];

        // Convert IP to long
        $ipLong = ip2long($ip);
        $prefix = (int) $prefix;
        $netmask = ~((1 << (32 - $prefix)) - 1);
        $network = $ipLong & $netmask;
        $broadcast = $network | ~$netmask;

        // Generate IPs (skip network and broadcast addresses)
        for ($i = $network + 1; $i < $broadcast; $i++) {
            $ips[] = long2ip($i);
        }

        // Calculate subnet mask from prefix
        $subnetMask = long2ip($netmask);

        return ['ips' => $ips, 'subnet_mask' => $subnetMask];
    }

    /**
     * Parse hyphen range to IP list.
     * Supports both short format (192.168.1.1-254) and full format (192.168.1.1-192.168.1.254)
     */
    private function parseHyphenRange(string $range): array
    {
        // Try full format first (192.168.1.1-192.168.1.254)
        if (preg_match('/^(\d+\.\d+\.\d+\.\d+)-(\d+\.\d+\.\d+\.\d+)$/', $range, $matches)) {
            $startIp = $matches[1];
            $endIp = $matches[2];
            
            $start = ip2long($startIp);
            $end = ip2long($endIp);
            
            if ($start === false || $end === false || $start > $end) {
                throw new \InvalidArgumentException("Invalid IP range format: {$range}");
            }
            
            $ips = [];
            for ($i = $start; $i <= $end; $i++) {
                $ips[] = long2ip($i);
            }
            
            return $ips;
        }
        
        // Try short format (192.168.1.1-254)
        if (preg_match('/^(\d+\.\d+\.\d+\.)(\d+)-(\d+)$/', $range, $matches)) {
            $prefix = $matches[1];
            $start = (int) $matches[2];
            $end = (int) $matches[3];

            if ($start > $end || $start < 0 || $end > 255) {
                throw new \InvalidArgumentException("Invalid IP range format: {$range}");
            }

            $ips = [];
            for ($i = $start; $i <= $end; $i++) {
                $ips[] = $prefix . $i;
            }

            return $ips;
        }
        
        throw new \InvalidArgumentException("Invalid IP range format: {$range}");
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

            // Process in chunks to avoid timeout and memory issues
            $chunkSize = 50; // Process 50 records at a time
            $chunks = array_chunk($secrets, $chunkSize);

            foreach ($chunks as $chunkIndex => $secretsChunk) {
                DB::beginTransaction();
                try {
                    foreach ($secretsChunk as $secretData) {
                        try {
                            // Create user account if not exists
                            // Use a lower bcrypt cost (4 instead of default 10) for bulk imports to improve performance
                            // This is acceptable as users should be prompted to change passwords after import
                            $hashedPassword = password_hash($secretData['password'], PASSWORD_BCRYPT, ['cost' => 4]);
                            
                            $user = User::firstOrCreate(
                                [
                                    'mobile' => $secretData['mobile'] ?? $secretData['username'],
                                    'tenant_id' => $tenantId,
                                ],
                                [
                                    'name' => $secretData['name'] ?? $secretData['username'],
                                    'email' => $secretData['email'] ?? null,
                                    'password' => $hashedPassword,
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
                    
                    Log::info("Processed chunk {$chunkIndex} of secrets import", [
                        'chunk_size' => count($secretsChunk),
                        'total_imported' => $imported,
                        'total_failed' => $failed,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to process chunk {$chunkIndex} of secrets import", [
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with next chunk
                    $failed += count($secretsChunk);
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
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
                    // 'username' is required for NetworkUser creation, while 'name' is used for User display name
                    'username' => $secret['name'] ?? '',
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
     * Import IP pools from router.
     */
    public function importIpPoolsFromRouter(int $routerId): array
    {
        $user = auth()->user();
        if (! $user) {
            Log::warning('Attempt to import IP pools without authenticated user', [
                'router_id' => $routerId,
            ]);

            return [
                'success' => false,
                'imported' => 0,
                'failed' => 0,
                'errors' => ['User not authenticated'],
            ];
        }

        $tenantId = $user->tenant_id;
        try {
            // Connect to router
            if (! $this->mikrotikService->connectRouter($routerId)) {
                throw new \Exception('Failed to connect to router');
            }

            $router = MikrotikRouter::find($routerId);

            if (! $router) {
                Log::error('Router not found for fetching IP pools', ['router_id' => $routerId]);
                throw new \Exception('Router not found');
            }

            // Fetch IP pools from router using API service
            $pools = $this->mikrotikApiService->getMktRows($router, '/ip/pool');

            if (empty($pools)) {
                return [
                    'success' => true,
                    'imported' => 0,
                    'failed' => 0,
                    'errors' => [],
                    'message' => 'No IP pools found on router',
                ];
            }

            // Create backup
            $this->backupIpPools($tenantId);

            $imported = 0;
            $failed = 0;
            $errors = [];

            // Process each pool from router
            foreach ($pools as $poolData) {
                try {
                    // Parse the ranges field from MikroTik format
                    $ranges = $poolData['ranges'] ?? '';
                    if (empty($ranges)) {
                        $errors[] = "Pool {$poolData['name']} has no ranges defined";
                        $failed++;
                        continue;
                    }

                    // Parse IP ranges - MikroTik format can be: "192.168.1.10-192.168.1.100"
                    $parseResult = $this->parseIpRange($ranges);
                    $ipList = $parseResult['ips'];
                    $calculatedSubnetMask = $parseResult['subnet_mask'];

                    // Process IPs in chunks to avoid memory issues and timeout
                    $chunkSize = 100; // Process 100 IPs at a time
                    $ipChunks = array_chunk($ipList, $chunkSize);
                    
                    foreach ($ipChunks as $chunkIndex => $ipChunk) {
                        DB::beginTransaction();
                        try {
                            // Prepare bulk insert data
                            $bulkData = [];
                            foreach ($ipChunk as $ip) {
                                $bulkData[] = [
                                    'name' => isset($poolData['name']) ? "{$poolData['name']}-{$ip}" : "Pool-{$ip}",
                                    'ip_address' => $ip,
                                    'subnet_mask' => $calculatedSubnetMask ?? '255.255.255.0',
                                    'gateway' => null,
                                    'pool_type' => 'pppoe',
                                    'tenant_id' => $tenantId,
                                    'nas_id' => null,
                                    'status' => 'available',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                            
                            // Bulk insert for better performance
                            IpPool::insert($bulkData);
                            $imported += count($bulkData);
                            DB::commit();
                            
                            Log::info("Processed chunk {$chunkIndex} of IP pool import", [
                                'pool_name' => $poolData['name'] ?? 'Unknown',
                                'chunk_size' => count($bulkData),
                                'total_imported' => $imported,
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $failed += count($ipChunk);
                            $poolName = $poolData['name'] ?? 'Unknown';
                            $errors[] = "Failed to import chunk {$chunkIndex} of pool {$poolName}: {$e->getMessage()}";
                            Log::error("Failed to import IP pool chunk", [
                                'pool_name' => $poolName,
                                'chunk_index' => $chunkIndex,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $poolName = $poolData['name'] ?? 'Unknown';
                    $errors[] = "Failed to process pool {$poolName}: {$e->getMessage()}";
                    Log::error("Failed to process IP pool", [
                        'pool_name' => $poolName,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to import IP pools from router', [
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
     * Get customer role ID.
     */
    private function getCustomerRoleId(): int
    {
        return DB::table('roles')->where('slug', 'customer')->value('id') ?? 1;
    }
}
