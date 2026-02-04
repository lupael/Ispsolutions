<?php

namespace App\Contracts;

interface MikroTikServiceInterface
{
    /**
     * Connect to MikroTik router
     */
    public function connect(): bool;

    /**
     * Disconnect from MikroTik router
     */
    public function disconnect(): void;

    /**
     * Add a new PPPoE user
     */
    public function addPPPoEUser(
        string $username,
        string $password,
        string $profile,
        ?string $service = 'pppoe'
    ): bool;

    /**
     * Update an existing PPPoE user
     */
    public function updatePPPoEUser(string $username, array $data): bool;

    /**
     * Remove a PPPoE user
     */
    public function removePPPoEUser(string $username): bool;

    /**
     * Get list of active PPPoE sessions
     */
    public function getActiveSessions(): array;

    /**
     * Disconnect a specific session
     */
    public function disconnectSession(string $sessionId): bool;

    /**
     * Get available PPPoE profiles
     */
    public function getProfiles(): array;

    /**
     * Check router connectivity and health
     */
    public function healthCheck(): bool;
}
