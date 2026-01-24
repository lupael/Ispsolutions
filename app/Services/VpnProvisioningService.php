<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\MikrotikVpnAccount;
use App\Models\VpnPool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VpnProvisioningService
{
    private const PORT_RANGE_START = 5001;
    private const PORT_RANGE_END = 5500;
    private const DEFAULT_RATE_LIMIT = '5M/5M'; // 5 Mbps default

    protected MikrotikService $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Create VPN account with automatic provisioning.
     */
    public function createVpnAccount(array $data): array
    {
        $tenantId = auth()->user()->tenant_id;
        $routerId = $data['router_id'];

        DB::beginTransaction();
        try {
            // 1. Generate credentials if not provided
            $username = $data['username'] ?? $this->generateUsername();
            $password = $data['password'] ?? $this->generatePassword();

            // 2. Allocate IP from pool
            $ipAddress = $this->allocateIpFromPool($data['pool_id'], $tenantId);
            if (!$ipAddress) {
                throw new \Exception('No available IP addresses in the selected pool');
            }

            // 3. Allocate port for Winbox forwarding
            $forwardingPort = $this->allocatePort($tenantId);
            if (!$forwardingPort) {
                throw new \Exception('No available ports for forwarding');
            }

            // 4. Create VPN account record
            $vpnAccount = MikrotikVpnAccount::create([
                'username' => $username,
                'password' => $password,
                'remote_address' => $ipAddress,
                'local_address' => $data['local_address'] ?? null,
                'protocol' => $data['protocol'] ?? 'pptp',
                'pool_id' => $data['pool_id'],
                'router_id' => $routerId,
                'tenant_id' => $tenantId,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'forwarding_port' => $forwardingPort,
                'is_active' => true,
            ]);

            // 5. Add RADIUS attributes
            $this->addRadiusAttributes($username, $password, $ipAddress);

            // 6. Add NAT rule for Winbox port forwarding
            $this->addNatRule($routerId, $ipAddress, $forwardingPort);

            DB::commit();

            return [
                'success' => true,
                'account' => $vpnAccount,
                'message' => 'VPN account created successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create VPN account', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create VPN account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete VPN account with cleanup.
     */
    public function deleteVpnAccount(int $accountId): array
    {
        DB::beginTransaction();
        try {
            $account = MikrotikVpnAccount::findOrFail($accountId);

            // 1. Remove RADIUS attributes
            $this->removeRadiusAttributes($account->username);

            // 2. Remove NAT rule
            $this->removeNatRule($account->router_id, $account->forwarding_port);

            // 3. Release IP back to pool
            $this->releaseIpToPool($account->remote_address, $account->pool_id);

            // 4. Release port
            $this->releasePort($account->forwarding_port, auth()->user()->tenant_id);

            // 5. Delete account
            $account->delete();

            DB::commit();

            Log::info('VPN account deleted successfully', [
                'account_id' => $accountId,
                'username' => $account->username,
            ]);

            return [
                'success' => true,
                'message' => 'VPN account deleted successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete VPN account', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete VPN account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate random username (8 characters).
     */
    private function generateUsername(): string
    {
        return 'vpn_' . Str::random(8);
    }

    /**
     * Generate secure random password (12 characters).
     */
    private function generatePassword(): string
    {
        return Str::password(12, true, true, true);
    }

    /**
     * Allocate IP from pool.
     */
    private function allocateIpFromPool(int $poolId, int $tenantId): ?string
    {
        // Get pool
        $pool = VpnPool::find($poolId);
        if (!$pool) {
            return null;
        }

        // Find available IP
        $usedIps = DB::table('vpn_pool_allocations')
            ->where('pool_id', $poolId)
            ->where('is_allocated', true)
            ->pluck('ip_address')
            ->toArray();

        // Parse pool range and find first available IP
        $poolIps = $this->parseIpRange($pool->ip_range);
        foreach ($poolIps as $ip) {
            if (!in_array($ip, $usedIps)) {
                // Mark as allocated
                DB::table('vpn_pool_allocations')->insert([
                    'pool_id' => $poolId,
                    'ip_address' => $ip,
                    'is_allocated' => true,
                    'allocated_at' => now(),
                    'tenant_id' => $tenantId,
                ]);
                return $ip;
            }
        }

        return null;
    }

    /**
     * Release IP back to pool.
     */
    private function releaseIpToPool(string $ipAddress, int $poolId): void
    {
        DB::table('vpn_pool_allocations')
            ->where('pool_id', $poolId)
            ->where('ip_address', $ipAddress)
            ->delete();
    }

    /**
     * Allocate port for forwarding.
     */
    private function allocatePort(int $tenantId): ?int
    {
        $usedPorts = DB::table('mikrotik_vpn_accounts')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('forwarding_port')
            ->pluck('forwarding_port')
            ->toArray();

        for ($port = self::PORT_RANGE_START; $port <= self::PORT_RANGE_END; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }

        return null;
    }

    /**
     * Release port.
     */
    private function releasePort(int $port, int $tenantId): void
    {
        // Port is automatically released when account is deleted
        // This is just for logging
        Log::info('Port released', [
            'port' => $port,
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Add RADIUS attributes for VPN user.
     */
    private function addRadiusAttributes(string $username, string $password, string $ipAddress): void
    {
        // Add to radcheck (authentication) using radius connection
        DB::connection('radius')->table('radcheck')->insert([
            [
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $password,
            ],
            [
                'username' => $username,
                'attribute' => 'Framed-IP-Address',
                'op' => ':=',
                'value' => $ipAddress,
            ],
        ]);

        // Add to radreply (rate limit) using radius connection and correct operator
        DB::connection('radius')->table('radreply')->insert([
            'username' => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => '=',
            'value' => self::DEFAULT_RATE_LIMIT,
        ]);
    }

    /**
     * Remove RADIUS attributes.
     */
    private function removeRadiusAttributes(string $username): void
    {
        DB::connection('radius')->table('radcheck')->where('username', $username)->delete();
        DB::connection('radius')->table('radreply')->where('username', $username)->delete();
    }

    /**
     * Add NAT rule for Winbox port forwarding.
     */
    private function addNatRule(int $routerId, string $internalIp, int $externalPort): void
    {
        try {
            // Connect to router
            $router = MikrotikRouter::find($routerId);
            if (!$router) {
                throw new \Exception('Router not found');
            }

            // In production, use RouterOS API to add NAT rule
            // For now, just log the action
            Log::info('NAT rule should be added', [
                'router_id' => $routerId,
                'internal_ip' => $internalIp,
                'external_port' => $externalPort,
                'rule' => "chain=dstnat protocol=tcp dst-port={$externalPort} action=dst-nat to-addresses={$internalIp} to-ports=8291",
            ]);

            // Example RouterOS API command:
            // /ip firewall nat add chain=dstnat protocol=tcp dst-port={$externalPort} \
            //   action=dst-nat to-addresses={$internalIp} to-ports=8291 \
            //   comment="VPN Winbox Forwarding"
        } catch (\Exception $e) {
            Log::error('Failed to add NAT rule', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - NAT rule can be added manually if needed
        }
    }

    /**
     * Remove NAT rule.
     */
    private function removeNatRule(int $routerId, int $externalPort): void
    {
        try {
            // In production, use RouterOS API to remove NAT rule
            Log::info('NAT rule should be removed', [
                'router_id' => $routerId,
                'external_port' => $externalPort,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove NAT rule', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parse IP range to array of IPs.
     */
    private function parseIpRange(string $range): array
    {
        // Simple implementation - can be enhanced
        if (str_contains($range, '-')) {
            // Format: 192.168.1.1-254
            preg_match('/^(\d+\.\d+\.\d+\.)(\d+)-(\d+)$/', $range, $matches);
            if (count($matches) === 4) {
                $prefix = $matches[1];
                $start = (int) $matches[2];
                $end = (int) $matches[3];
                
                $ips = [];
                for ($i = $start; $i <= $end; $i++) {
                    $ips[] = $prefix . $i;
                }
                return $ips;
            }
        }

        return [$range]; // Single IP
    }
}
