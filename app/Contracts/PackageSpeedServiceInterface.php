<?php

declare(strict_types=1);

namespace App\Contracts;

interface PackageSpeedServiceInterface
{
    /**
     * Map package to router profile
     */
    public function mapPackageToProfile(int $packageId, int $routerId, string $profileName): bool;

    /**
     * Apply speed to user
     */
    public function applySpeedToUser(int $userId, string $method = 'router'): bool;

    /**
     * Get mapped profile for package
     */
    public function getProfileForPackage(int $packageId, int $routerId): ?string;
}
