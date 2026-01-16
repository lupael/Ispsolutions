<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\IpAllocation;

interface IpamServiceInterface
{
    /**
     * Allocate an IP address from a subnet
     *
     * @param int $subnetId
     * @param string $macAddress
     * @param string $username
     * @return IpAllocation|null
     */
    public function allocateIP(int $subnetId, string $macAddress, string $username): ?IpAllocation;

    /**
     * Release an allocated IP address
     *
     * @param int $allocationId
     * @return bool
     */
    public function releaseIP(int $allocationId): bool;

    /**
     * Get list of available IP addresses in a subnet
     *
     * @param int $subnetId
     * @return array<string>
     */
    public function getAvailableIPs(int $subnetId): array;

    /**
     * Get utilization statistics for a pool
     *
     * @param int $poolId
     * @return array{total: int, allocated: int, available: int, utilization_percent: float}
     */
    public function getPoolUtilization(int $poolId): array;
}
