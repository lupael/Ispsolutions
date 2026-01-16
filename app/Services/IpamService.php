<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\IpamServiceInterface;
use App\Models\IpAllocation;

class IpamService implements IpamServiceInterface
{
    /**
     * @inheritDoc
     */
    public function allocateIP(int $subnetId, string $macAddress, string $username): ?IpAllocation
    {
        // Implementation will be added in Phase 4
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function releaseIP(int $allocationId): bool
    {
        // Implementation will be added in Phase 4
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getAvailableIPs(int $subnetId): array
    {
        // Implementation will be added in Phase 4
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getPoolUtilization(int $poolId): array
    {
        // Implementation will be added in Phase 4
        throw new \RuntimeException('Not yet implemented');
    }
}
