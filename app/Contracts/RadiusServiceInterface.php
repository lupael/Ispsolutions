<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\NetworkUser;

interface RadiusServiceInterface
{
    /**
     * Create a new RADIUS user
     *
     * @param string $username
     * @param string $password
     * @param array<string, mixed> $attributes
     * @return bool
     */
    public function createUser(string $username, string $password, array $attributes = []): bool;

    /**
     * Update an existing RADIUS user
     *
     * @param string $username
     * @param array<string, mixed> $attributes
     * @return bool
     */
    public function updateUser(string $username, array $attributes): bool;

    /**
     * Delete a RADIUS user
     *
     * @param string $username
     * @return bool
     */
    public function deleteUser(string $username): bool;

    /**
     * Sync a network user to RADIUS
     *
     * @param NetworkUser $user
     * @return bool
     */
    public function syncUser(NetworkUser $user): bool;

    /**
     * Get accounting data for a user
     *
     * @param string $username
     * @return array<string, mixed>
     */
    public function getAccountingData(string $username): array;
}
