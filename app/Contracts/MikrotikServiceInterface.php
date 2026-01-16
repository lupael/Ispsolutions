<?php

declare(strict_types=1);

namespace App\Contracts;

interface MikrotikServiceInterface
{
    /**
     * Connect to a MikroTik router
     *
     * @param int $routerId
     * @return bool
     */
    public function connectRouter(int $routerId): bool;

    /**
     * Create a PPPoE user on MikroTik
     *
     * @param array<string, mixed> $userData
     * @return bool
     */
    public function createPppoeUser(array $userData): bool;

    /**
     * Update a PPPoE user on MikroTik
     *
     * @param string $username
     * @param array<string, mixed> $userData
     * @return bool
     */
    public function updatePppoeUser(string $username, array $userData): bool;

    /**
     * Delete a PPPoE user from MikroTik
     *
     * @param string $username
     * @return bool
     */
    public function deletePppoeUser(string $username): bool;

    /**
     * Get active sessions from MikroTik
     *
     * @param int $routerId
     * @return array<int, array<string, mixed>>
     */
    public function getActiveSessions(int $routerId): array;

    /**
     * Disconnect a session on MikroTik
     *
     * @param string $sessionId
     * @return bool
     */
    public function disconnectSession(string $sessionId): bool;
}
