<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\NetworkUser;

interface RadiusServiceInterface
{
    /**
     * Create a new RADIUS user
     *
     * @param array<string, mixed> $attributes
     */
    public function createUser(string $username, string $password, array $attributes = []): bool;

    /**
     * Update an existing RADIUS user
     *
     * @param array<string, mixed> $attributes
     */
    public function updateUser(string $username, array $attributes): bool;

    /**
     * Delete a RADIUS user
     */
    public function deleteUser(string $username): bool;

    /**
     * Sync a network user to RADIUS
     */
    public function syncUser(NetworkUser $user): bool;

    /**
     * Get accounting data for a user
     *
     * @return array<string, mixed>
     */
    public function getAccountingData(string $username): array;
}
