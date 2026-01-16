<?php

declare(strict_types=1);

namespace App\Contracts;

interface MikrotikServiceInterface
{
    /**
     * Connect to a MikroTik router
     */
    public function connectRouter(int $routerId): bool;

    /**
     * Create a PPPoE user on MikroTik
     *
     * @param array<string, mixed> $userData
     */
    public function createPppoeUser(array $userData): bool;

    /**
     * Update a PPPoE user on MikroTik
     *
     * @param array<string, mixed> $userData
     */
    public function updatePppoeUser(string $username, array $userData): bool;

    /**
     * Delete a PPPoE user from MikroTik
     */
    public function deletePppoeUser(string $username): bool;

    /**
     * Get active sessions from MikroTik
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveSessions(int $routerId): array;

    /**
     * Disconnect a session on MikroTik
     */
    public function disconnectSession(string $sessionId): bool;
}
