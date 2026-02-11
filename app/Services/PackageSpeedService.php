<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;
use App\Contracts\PackageSpeedServiceInterface;
use App\Models\User;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use Illuminate\Support\Facades\Log;

class PackageSpeedService implements PackageSpeedServiceInterface
{
    public function __construct(
        private readonly MikrotikServiceInterface $mikrotikService
    ) {}

    /**
     * {@inheritDoc}
     */
    public function mapPackageToProfile(int $packageId, int $routerId, string $profileName): bool
    {
        try {
            $package = Package::find($packageId);

            if (! $package) {
                Log::error('Package not found', ['package_id' => $packageId]);

                return false;
            }

            PackageProfileMapping::updateOrCreate(
                [
                    'package_id' => $packageId,
                    'router_id' => $routerId,
                ],
                [
                    'profile_name' => $profileName,
                    'speed_control_method' => 'router',
                ]
            );

            Log::info('Package mapped to profile', [
                'package_id' => $packageId,
                'router_id' => $routerId,
                'profile' => $profileName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error mapping package to profile', [
                'package_id' => $packageId,
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function applySpeedToUser(int $userId, string $method = 'router'): bool
    {
        try {
            $user = User::with('servicePackage')->find($userId);

            if (! $user || ! $user->servicePackage || !$user->is_subscriber) {
                Log::error('User, package not found, or user is not a subscriber', ['user_id' => $userId]);

                return false;
            }

            if ($method === 'router') {
                return $this->applySpeedViaRouter($user);
            }

            Log::warning('Speed control method not supported', ['method' => $method]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error applying speed to user', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProfileForPackage(int $packageId, int $routerId): ?string
    {
        try {
            $mapping = PackageProfileMapping::where('package_id', $packageId)
                ->where('router_id', $routerId)
                ->first();

            return $mapping?->profile_name;
        } catch (\Exception $e) {
            Log::error('Error getting profile for package', [
                'package_id' => $packageId,
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Apply speed via router
     */
    private function applySpeedViaRouter(User $user): bool
    {
        try {
            if (! $user->router_id) {
                Log::error('User has no router assigned', ['user_id' => $user->id]);

                return false;
            }

            $profile = $this->getProfileForPackage($user->service_package_id, $user->router_id);

            if (! $profile) {
                Log::warning('No profile mapping found for package', [
                    'user_id' => $user->id,
                    'package_id' => $user->service_package_id,
                    'router_id' => $user->router_id,
                ]);

                return false;
            }

            $userData = [
                'router_id' => $user->router_id,
                'username' => $user->username,
                'password' => $user->radius_password,
                'profile' => $profile,
                'service' => 'pppoe',
            ];

            $success = $this->mikrotikService->updatePppoeUser($user->username, $userData);

            if ($success) {
                Log::info('Speed applied to user via router', [
                    'user_id' => $user->id,
                    'profile' => $profile,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error applying speed via router', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}