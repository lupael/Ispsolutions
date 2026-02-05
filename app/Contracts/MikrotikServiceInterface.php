<?php

declare(strict_types=1);

namespace App\Contracts;

interface MikrotikServiceInterface
{
    /**
     * Connect to a MikroTik router.
     *
     * @param int $routerId
     * @return bool
     */
    public function connectRouter(int $routerId): bool;

    /**
     * Create a PPPoE user on the router.
     *
     * @param array $userData
     * @return bool
     */
    public function createPppoeUser(array $userData): bool;

    /**
     * Update a PPPoE user on the router.
     *
     * @param string $username
     * @param array $userData
     * @return bool
     */
    public function updatePppoeUser(string $username, array $userData): bool;

    /**
     * Delete a PPPoE user from the router.
     *
     * @param string $username
     * @return bool
     */
    public function deletePppoeUser(string $username): bool;

    /**
     * Get active PPPoE sessions from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getActiveSessions(int $routerId): array;

    /**
     * Disconnect a PPPoE session on the router.
     *
     * @param string $sessionId
     * @return bool
     */
    public function disconnectSession(string $sessionId): bool;

    /**
     * Get PPPoE profiles from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getProfiles(int $routerId): array;

    /**
     * Create a PPPoE profile on the router.
     *
     * @param int $routerId
     * @param array $profileData
     * @return bool
     */
    public function createPppProfile(int $routerId, array $profileData): bool;

    /**
     * Import PPPoE profiles from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function importProfiles(int $routerId): array;

    /**
     * Synchronize PPPoE profiles with the local database.
     *
     * @param int $routerId
     * @return int
     */
    public function syncProfiles(int $routerId): int;

    /**
     * Create an IP pool on the router.
     *
     * @param int $routerId
     * @param array $poolData
     * @return bool
     */
    public function createIpPool(int $routerId, array $poolData): bool;

    /**
     * Import IP pools from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function importIpPools(int $routerId): array;

    /**
     * Synchronize IP pools with the local database.
     *
     * @param int $routerId
     * @return int
     */
    public function syncIpPools(int $routerId): int;

    /**
     * Import PPP secrets from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function importSecrets(int $routerId): array;

    /**
     * Synchronize PPP secrets with the local database.
     *
     * @param int $routerId
     * @return int
     */
    public function syncSecrets(int $routerId): int;

    /**
     * Configure the router with a set of settings.
     *
     * @param int $routerId
     * @param array $config
     * @return bool
     */
    public function configureRouter(int $routerId, array $config): bool;

    /**
     * Create a VPN account on the router.
     *
     * @param int $routerId
     * @param array $vpnData
     * @return bool
     */
    public function createVpnAccount(int $routerId, array $vpnData): bool;

    /**
     * Get the VPN status from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getVpnStatus(int $routerId): array;

    /**
     * Create a queue on the router.
     *
     * @param int $routerId
     * @param array $queueData
     * @return bool
     */
    public function createQueue(int $routerId, array $queueData): bool;

    /**
     * Get queues from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getQueues(int $routerId): array;

    /**
     * Add a firewall rule to the router.
     *
     * @param int $routerId
     * @param array $ruleData
     * @return bool
     */
    public function addFirewallRule(int $routerId, array $ruleData): bool;

    /**
     * Get firewall rules from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getFirewallRules(int $routerId): array;

    /**
     * Get system resources from the router.
     *
     * @param int $routerId
     * @return array
     */
    public function getResources(int $routerId): array;
}
