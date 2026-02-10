<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

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
     *
     * @param array<string, mixed> $attributes
     */
    public function syncUser(User $user, array $attributes = []): bool;

    /**
     * Get accounting data for a user
     *
     * @return array<string, mixed>
     */
    public function getAccountingData(string $username): array;

    /**
     * Authenticate a user
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function authenticate(array $data): array;

    /**
     * Start accounting session
     *
     * @param array<string, mixed> $data
     */
    public function accountingStart(array $data): bool;

    /**
     * Update accounting session
     *
     * @param array<string, mixed> $data
     */
    public function accountingUpdate(array $data): bool;

    /**
     * Stop accounting session
     *
     * @param array<string, mixed> $data
     */
    public function accountingStop(array $data): bool;

    /**
     * Get user statistics
     *
     * @return array<string, mixed>
     */
    public function getUserStats(string $username): array;
}
