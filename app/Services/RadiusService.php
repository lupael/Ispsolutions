<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RadiusServiceInterface;
use App\Models\NetworkUser;

class RadiusService implements RadiusServiceInterface
{
    /**
     * @inheritDoc
     */
    public function createUser(string $username, string $password, array $attributes = []): bool
    {
        // Implementation will be added in Phase 5
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function updateUser(string $username, array $attributes): bool
    {
        // Implementation will be added in Phase 5
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function deleteUser(string $username): bool
    {
        // Implementation will be added in Phase 5
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function syncUser(NetworkUser $user): bool
    {
        // Implementation will be added in Phase 5
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getAccountingData(string $username): array
    {
        // Implementation will be added in Phase 5
        throw new \RuntimeException('Not yet implemented');
    }
}
