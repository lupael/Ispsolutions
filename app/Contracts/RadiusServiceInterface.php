<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface RadiusServiceInterface
{
    public function createUser(string $username, string $password, array $attributes = []): bool;
    public function updateUser(string $username, array $attributes): bool;
    public function deleteUser(string $username): bool;
    public function syncUser(User $user, array $attributes = []): bool;
    public function getAccountingData(string $username): array;
    public function authenticate(array $data): array;
    public function accountingStart(array $data): bool;
    public function accountingUpdate(array $data): bool;
    public function accountingStop(array $data): bool;
    public function getUserStats(string $username): array;
}
