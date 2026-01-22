<?php

namespace App\Services;

use App\Models\MikrotikVpnAccount;
use App\Models\VpnPool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VpnService
{
    /**
     * Create VPN account
     */
    public function createVpnAccount(array $data): MikrotikVpnAccount
    {
        return DB::transaction(function () use ($data) {
            // Get available pool
            $pool = VpnPool::active()
                ->where('tenant_id', $data['tenant_id'])
                ->where('protocol', $data['protocol'] ?? 'pptp')
                ->where('total_ips', '>', DB::raw('used_ips'))
                ->first();

            if (! $pool) {
                throw new \Exception('No available VPN pool found');
            }

            // Create VPN account using actual model fields
            $vpnAccount = MikrotikVpnAccount::create([
                'router_id' => $data['router_id'],
                'username' => $data['username'],
                'password' => $data['password'],
                'profile' => $data['profile'] ?? 'default',
                'enabled' => $data['enabled'] ?? true,
            ]);

            // Update pool usage
            $pool->increment('used_ips');

            Log::info('VPN account created', [
                'username' => $vpnAccount->username,
                'pool_id' => $pool->id,
            ]);

            return $vpnAccount;
        });
    }

    /**
     * Release VPN account
     */
    public function releaseVpnAccount(MikrotikVpnAccount $vpnAccount): bool
    {
        return DB::transaction(function () use ($vpnAccount) {
            // Deactivate account
            $vpnAccount->update(['enabled' => false]);

            Log::info('VPN account released', [
                'username' => $vpnAccount->username,
            ]);

            return true;
        });
    }

    /**
     * Change VPN password
     */
    public function changePassword(MikrotikVpnAccount $vpnAccount, string $newPassword): MikrotikVpnAccount
    {
        $vpnAccount->update(['password' => $newPassword]);

        Log::info('VPN password changed', [
            'username' => $vpnAccount->username,
        ]);

        return $vpnAccount->fresh();
    }

    /**
     * Get VPN pool statistics
     */
    public function getPoolStatistics(int $poolId): array
    {
        $pool = VpnPool::findOrFail($poolId);

        return [
            'pool_name' => $pool->name,
            'protocol' => $pool->protocol,
            'total_ips' => $pool->total_ips,
            'used_ips' => $pool->used_ips,
            'available_ips' => $pool->available_ips,
            'usage_percentage' => $pool->usage_percentage,
            'active_accounts' => MikrotikVpnAccount::whereHas('router', function ($query) use ($pool) {
                $query->where('tenant_id', $pool->tenant_id);
            })->where('enabled', true)->count(),
            'total_accounts' => MikrotikVpnAccount::whereHas('router', function ($query) use ($pool) {
                $query->where('tenant_id', $pool->tenant_id);
            })->count(),
        ];
    }

    /**
     * Get active VPN connections
     */
    public function getActiveConnections(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        // Note: MikrotikVpnAccount doesn't have tenant_id field, filter by router's tenant
        return MikrotikVpnAccount::where('enabled', true)
            ->whereHas('router', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->with(['router'])
            ->get();
    }

    /**
     * Monitor VPN pool capacity
     */
    public function checkPoolCapacity(int $poolId, int $threshold = 80): array
    {
        $pool = VpnPool::findOrFail($poolId);
        $usagePercentage = $pool->usage_percentage;

        return [
            'pool_id' => $pool->id,
            'pool_name' => $pool->name,
            'usage_percentage' => $usagePercentage,
            'is_critical' => $usagePercentage >= $threshold,
            'available_ips' => $pool->available_ips,
            'recommendation' => $usagePercentage >= $threshold
                ? 'Pool capacity is critical. Consider adding more IPs or creating a new pool.'
                : 'Pool capacity is adequate.',
        ];
    }

    /**
     * Create VPN pool
     */
    public function createPool(array $data): VpnPool
    {
        // Calculate total IPs
        $startIp = ip2long($data['start_ip']);
        $endIp = ip2long($data['end_ip']);

        if ($startIp === false || $endIp === false || $startIp > $endIp) {
            throw new \Exception('Invalid IP range');
        }

        $totalIps = $endIp - $startIp + 1;

        return VpnPool::create([
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'network' => $data['network'],
            'subnet_mask' => $data['subnet_mask'],
            'start_ip' => $data['start_ip'],
            'end_ip' => $data['end_ip'],
            'gateway' => $data['gateway'] ?? null,
            'dns_primary' => $data['dns_primary'] ?? null,
            'dns_secondary' => $data['dns_secondary'] ?? null,
            'protocol' => $data['protocol'] ?? 'pptp',
            'is_active' => $data['is_active'] ?? true,
            'total_ips' => $totalIps,
            'used_ips' => 0,
        ]);
    }

    /**
     * Update pool
     */
    public function updatePool(VpnPool $pool, array $data): VpnPool
    {
        // Recalculate total IPs if range changed
        if (isset($data['start_ip']) || isset($data['end_ip'])) {
            $startIp = ip2long($data['start_ip'] ?? $pool->start_ip);
            $endIp = ip2long($data['end_ip'] ?? $pool->end_ip);

            if ($startIp === false || $endIp === false || $startIp > $endIp) {
                throw new \Exception('Invalid IP range');
            }

            $data['total_ips'] = $endIp - $startIp + 1;

            // Check if new range can accommodate existing allocations
            if ($data['total_ips'] < $pool->used_ips) {
                throw new \Exception('New IP range cannot accommodate existing allocations');
            }
        }

        $pool->update($data);

        return $pool->fresh();
    }

    /**
     * Get pool utilization report
     */
    public function getUtilizationReport(int $tenantId): array
    {
        $pools = VpnPool::where('tenant_id', $tenantId)->get();

        return $pools->map(function ($pool) {
            return [
                'pool_id' => $pool->id,
                'pool_name' => $pool->name,
                'protocol' => $pool->protocol,
                'total_ips' => $pool->total_ips,
                'used_ips' => $pool->used_ips,
                'available_ips' => $pool->available_ips,
                'usage_percentage' => $pool->usage_percentage,
                'status' => $pool->is_active ? 'Active' : 'Inactive',
            ];
        })->toArray();
    }
}
