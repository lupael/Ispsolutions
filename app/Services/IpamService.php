<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\IpamServiceInterface;
use App\Models\IpAllocation;
use App\Models\IpAllocationHistory;
use App\Models\IpPool;
use App\Models\IpSubnet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpamService implements IpamServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function allocateIP(int $subnetId, string $macAddress, string $username): ?IpAllocation
    {
        return DB::transaction(function () use ($subnetId, $macAddress, $username) {
            // Get subnet with lock to prevent concurrent allocations
            $subnet = IpSubnet::where('id', $subnetId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $subnet) {
                Log::error('Subnet not found or inactive', ['subnet_id' => $subnetId]);

                return;
            }

            // Get the first available IP
            $availableIP = $this->findFirstAvailableIP($subnet);

            if (! $availableIP) {
                Log::warning('No available IPs in subnet', ['subnet_id' => $subnetId]);

                return;
            }

            // Create the allocation
            $allocation = IpAllocation::create([
                'subnet_id' => $subnetId,
                'ip_address' => $availableIP,
                'mac_address' => $macAddress,
                'username' => $username,
                'allocated_at' => now(),
                'status' => 'allocated',
            ]);

            // Log to history
            IpAllocationHistory::create([
                'allocation_id' => $allocation->id,
                'ip_address' => $availableIP,
                'mac_address' => $macAddress,
                'username' => $username,
                'action' => 'allocated',
                'allocated_at' => now(),
            ]);

            Log::info('IP allocated', [
                'allocation_id' => $allocation->id,
                'ip' => $availableIP,
                'username' => $username,
            ]);

            return $allocation;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function releaseIP(int $allocationId): bool
    {
        return DB::transaction(function () use ($allocationId) {
            $allocation = IpAllocation::lockForUpdate()->find($allocationId);

            if (! $allocation) {
                Log::error('Allocation not found', ['allocation_id' => $allocationId]);

                return false;
            }

            if ($allocation->status === 'released') {
                Log::warning('Allocation already released', ['allocation_id' => $allocationId]);

                return false;
            }

            // Update allocation status
            $allocation->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            // Log to history
            IpAllocationHistory::create([
                'allocation_id' => $allocation->id,
                'ip_address' => $allocation->ip_address,
                'mac_address' => $allocation->mac_address,
                'username' => $allocation->username,
                'action' => 'released',
                'allocated_at' => $allocation->allocated_at,
                'released_at' => now(),
            ]);

            Log::info('IP released', [
                'allocation_id' => $allocationId,
                'ip' => $allocation->ip_address,
            ]);

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableIPs(int $subnetId): array
    {
        $subnet = IpSubnet::find($subnetId);

        if (! $subnet) {
            return [];
        }

        $allIPs = $this->generateIPRange($subnet->network, $subnet->prefix_length);

        // Get allocated IPs
        $allocatedIPs = IpAllocation::where('subnet_id', $subnetId)
            ->where('status', 'allocated')
            ->pluck('ip_address')
            ->toArray();

        // Return available IPs (all IPs minus allocated ones)
        return array_values(array_diff($allIPs, $allocatedIPs));
    }

    /**
     * {@inheritDoc}
     */
    public function getPoolUtilization(int $poolId): array
    {
        $pool = IpPool::find($poolId);

        if (! $pool) {
            return [
                'total' => 0,
                'allocated' => 0,
                'available' => 0,
                'utilization_percent' => 0.0,
            ];
        }

        // Get all subnets for the pool with their allocation counts in a single query
        $subnets = IpSubnet::where('pool_id', $poolId)
            ->withCount(['allocations' => function ($query) {
                $query->where('status', 'allocated');
            }])
            ->get();

        $totalIPs = 0;
        $allocatedIPs = 0;

        foreach ($subnets as $subnet) {
            $subnetIPs = $this->calculateSubnetSize($subnet->prefix_length);
            $totalIPs += $subnetIPs;
            $allocatedIPs += $subnet->allocations_count;
        }

        $availableIPs = $totalIPs - $allocatedIPs;
        $utilizationPercent = $totalIPs > 0 ? ($allocatedIPs / $totalIPs) * 100 : 0.0;

        return [
            'total' => $totalIPs,
            'allocated' => $allocatedIPs,
            'available' => $availableIPs,
            'utilization_percent' => round($utilizationPercent, 2),
        ];
    }

    /**
     * Find the first available IP in a subnet
     */
    private function findFirstAvailableIP(IpSubnet $subnet): ?string
    {
        $allIPs = $this->generateIPRange($subnet->network, $subnet->prefix_length);

        // Fetch all allocated IPs for this subnet in one query
        $allocatedIPs = IpAllocation::where('subnet_id', $subnet->id)
            ->where('status', 'allocated')
            ->pluck('ip_address')
            ->flip()
            ->toArray();

        // Find first IP not in allocated set
        foreach ($allIPs as $ip) {
            if (! isset($allocatedIPs[$ip])) {
                return $ip;
            }
        }

        return null;
    }

    /**
     * Generate IP range from network and prefix length
     *
     * Note: This method currently only supports IPv4 addresses.
     * IPv6 support is not yet implemented.
     *
     * @return array<string>
     */
    private function generateIPRange(string $network, int $prefixLength): array
    {
        $ips = [];
        $networkLong = ip2long($network);
        $hostBits = 32 - $prefixLength;
        $numHosts = pow(2, $hostBits);

        // Skip network address and broadcast address
        $start = $networkLong + 1;
        $end = $networkLong + $numHosts - 2;

        for ($i = $start; $i <= $end; $i++) {
            $ips[] = long2ip((int) $i);
        }

        return $ips;
    }

    /**
     * {@inheritDoc}
     */
    public function detectOverlap(string $cidr, ?int $excludeSubnetId = null): array
    {
        // Parse CIDR notation
        [$network, $prefixLength] = explode('/', $cidr);

        // Query for potential overlapping subnets
        $query = IpSubnet::where('network_address', $network)
            ->where('prefix_length', (int) $prefixLength);

        // Exclude a specific subnet (useful for updates)
        if ($excludeSubnetId !== null) {
            $query->where('id', '!=', $excludeSubnetId);
        }

        return $query->get()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function cleanupExpiredAllocations(int $days): array
    {
        $expiredDate = now()->subDays($days);

        // Delete expired allocations
        $expiredCount = IpAllocation::where('expires_at', '<', $expiredDate)
            ->where('status', 'expired')
            ->delete();

        // Delete old history records
        $historyCount = DB::table('ip_allocation_history')
            ->where('released_at', '<', $expiredDate)
            ->delete();

        return [
            'expired_count' => $expiredCount,
            'history_count' => $historyCount,
        ];
    }

    /**
     * Calculate the number of usable IPs in a subnet
     */
    private function calculateSubnetSize(int $prefixLength): int
    {
        $hostBits = 32 - $prefixLength;

        // Subtract 2 for network and broadcast addresses
        return max(0, pow(2, $hostBits) - 2);
    }
}
