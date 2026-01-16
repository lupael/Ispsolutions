<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;

class MikrotikService implements MikrotikServiceInterface
{
    /**
     * @inheritDoc
     */
    public function connectRouter(int $routerId): bool
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function createPppoeUser(array $userData): bool
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function updatePppoeUser(string $username, array $userData): bool
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function deletePppoeUser(string $username): bool
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getActiveSessions(int $routerId): array
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function disconnectSession(string $sessionId): bool
    {
        // Implementation will be added in Phase 6
        throw new \RuntimeException('Not yet implemented');
    }
}
