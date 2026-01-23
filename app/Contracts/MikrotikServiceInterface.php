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

    /**
     * Get PPPoE profiles from MikroTik
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProfiles(int $routerId): array;

    /**
     * Create a PPPoE profile on MikroTik
     *
     * @param array<string, mixed> $profileData
     */
    public function createPppProfile(int $routerId, array $profileData): bool;

    /**
     * Import profiles from MikroTik router
     *
     * @return array<int, array<string, mixed>>
     */
    public function importProfiles(int $routerId): array;

    /**
     * Sync profiles from router to database
     */
    public function syncProfiles(int $routerId): int;

    /**
     * Create an IP pool on MikroTik
     *
     * @param array<string, mixed> $poolData
     */
    public function createIpPool(int $routerId, array $poolData): bool;

    /**
     * Import IP pools from MikroTik router
     *
     * @return array<int, array<string, mixed>>
     */
    public function importIpPools(int $routerId): array;

    /**
     * Sync IP pools from router to database
     */
    public function syncIpPools(int $routerId): int;

    /**
     * Import PPPoE secrets from MikroTik router
     *
     * @return array<int, array<string, mixed>>
     */
    public function importSecrets(int $routerId): array;

    /**
     * Sync secrets from router to database
     */
    public function syncSecrets(int $routerId): int;

    /**
     * Configure router with one-click settings
     *
     * @param array<string, mixed> $config
     */
    public function configureRouter(int $routerId, array $config): bool;

    /**
     * Create a VPN account on MikroTik
     *
     * @param array<string, mixed> $vpnData
     */
    public function createVpnAccount(int $routerId, array $vpnData): bool;

    /**
     * Get VPN status from MikroTik
     *
     * @return array<string, mixed>
     */
    public function getVpnStatus(int $routerId): array;

    /**
     * Create a queue on MikroTik
     *
     * @param array<string, mixed> $queueData
     */
    public function createQueue(int $routerId, array $queueData): bool;

    /**
     * Get queues from MikroTik
     *
     * @return array<int, array<string, mixed>>
     */
    public function getQueues(int $routerId): array;

    /**
     * Add a firewall rule on MikroTik
     *
     * @param array<string, mixed> $ruleData
     */
    public function addFirewallRule(int $routerId, array $ruleData): bool;

    /**
     * Get firewall rules from MikroTik
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFirewallRules(int $routerId): array;
}
