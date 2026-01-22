<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Remember tenant-specific data with caching.
     */
    public function rememberTenantData(string $key, int $ttl, callable $callback): mixed
    {
        $tenantId = $this->getCurrentTenantId();
        $cacheKey = $this->getTenantCacheKey($tenantId, $key);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.tenants'), "tenant:{$tenantId}"])
                    ->remember($cacheKey, $ttl, $callback);
            }

            return Cache::remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache remember failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    /**
     * Forget tenant-specific cache by pattern.
     */
    public function forgetTenantCache(string $pattern = '*'): bool
    {
        $tenantId = $this->getCurrentTenantId();

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags("tenant:{$tenantId}")->flush();

                return true;
            }

            // Fallback: Clear specific keys
            $prefix = config('cache-config.prefixes.tenant');
            $keys = $this->getCacheKeysByPattern("{$prefix}:{$tenantId}:{$pattern}");

            foreach ($keys as $key) {
                Cache::forget($key);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Cache forget failed', [
                'tenant_id' => $tenantId,
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remember statistics data with caching.
     */
    public function rememberStats(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? config('cache-config.ttl.dashboard_stats');
        $tenantId = $this->getCurrentTenantId();
        $cacheKey = $this->getStatsCacheKey($tenantId, $key);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.tenants'), 'stats'])
                    ->remember($cacheKey, $ttl, $callback);
            }

            return Cache::remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache remember stats failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    /**
     * Cache dashboard statistics.
     */
    public function cacheDashboardStats(int $tenantId, array $stats): void
    {
        $key = $this->getDashboardCacheKey($tenantId);
        $ttl = config('cache-config.ttl.dashboard_stats');

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags(['dashboard', "tenant:{$tenantId}"])->put($key, $stats, $ttl);
            } else {
                Cache::put($key, $stats, $ttl);
            }
        } catch (\Exception $e) {
            Log::error('Cache dashboard stats failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached dashboard statistics.
     */
    public function getDashboardStats(int $tenantId): ?array
    {
        $key = $this->getDashboardCacheKey($tenantId);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags(['dashboard', "tenant:{$tenantId}"])->get($key);
            }

            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error('Get dashboard stats from cache failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cache packages for a tenant.
     */
    public function cachePackages(int $tenantId, mixed $packages): void
    {
        $key = $this->getPackagesCacheKey($tenantId);
        $ttl = config('cache-config.ttl.packages');

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags([config('cache-config.tags.packages'), "tenant:{$tenantId}"])
                    ->put($key, $packages, $ttl);
            } else {
                Cache::put($key, $packages, $ttl);
            }
        } catch (\Exception $e) {
            Log::error('Cache packages failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached packages.
     */
    public function getPackages(int $tenantId): mixed
    {
        $key = $this->getPackagesCacheKey($tenantId);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.packages'), "tenant:{$tenantId}"])->get($key);
            }

            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error('Get packages from cache failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cache payment gateways.
     */
    public function cachePaymentGateways(int $tenantId, mixed $gateways): void
    {
        $key = $this->getGatewaysCacheKey($tenantId);
        $ttl = config('cache-config.ttl.payment_gateways');

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags([config('cache-config.tags.gateways'), "tenant:{$tenantId}"])
                    ->put($key, $gateways, $ttl);
            } else {
                Cache::put($key, $gateways, $ttl);
            }
        } catch (\Exception $e) {
            Log::error('Cache payment gateways failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached payment gateways.
     */
    public function getPaymentGateways(int $tenantId): mixed
    {
        $key = $this->getGatewaysCacheKey($tenantId);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.gateways'), "tenant:{$tenantId}"])->get($key);
            }

            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error('Get payment gateways from cache failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cache user role permissions.
     */
    public function cacheRolePermissions(int $userId, array $permissions): void
    {
        $key = $this->getPermissionsCacheKey($userId);
        $ttl = config('cache-config.ttl.role_permissions');

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags([config('cache-config.tags.permissions'), "user:{$userId}"])
                    ->put($key, $permissions, $ttl);
            } else {
                Cache::put($key, $permissions, $ttl);
            }
        } catch (\Exception $e) {
            Log::error('Cache role permissions failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached role permissions.
     */
    public function getRolePermissions(int $userId): ?array
    {
        $key = $this->getPermissionsCacheKey($userId);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.permissions'), "user:{$userId}"])->get($key);
            }

            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error('Get role permissions from cache failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cache network device status.
     */
    public function cacheDeviceStatus(int $tenantId, int $deviceId, array $status): void
    {
        $key = $this->getDeviceStatusCacheKey($tenantId, $deviceId);
        $ttl = config('cache-config.ttl.device_status');

        try {
            if (config('cache-config.enable_tagging')) {
                Cache::tags([config('cache-config.tags.devices'), "tenant:{$tenantId}"])
                    ->put($key, $status, $ttl);
            } else {
                Cache::put($key, $status, $ttl);
            }
        } catch (\Exception $e) {
            Log::error('Cache device status failed', [
                'tenant_id' => $tenantId,
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cached device status.
     */
    public function getDeviceStatus(int $tenantId, int $deviceId): ?array
    {
        $key = $this->getDeviceStatusCacheKey($tenantId, $deviceId);

        try {
            if (config('cache-config.enable_tagging')) {
                return Cache::tags([config('cache-config.tags.devices'), "tenant:{$tenantId}"])->get($key);
            }

            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error('Get device status from cache failed', [
                'tenant_id' => $tenantId,
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Invalidate cache on model updates.
     */
    public function invalidateModelCache(string $model, int $tenantId): void
    {
        try {
            $tag = match ($model) {
                'Package' => config('cache-config.tags.packages'),
                'PaymentGateway' => config('cache-config.tags.gateways'),
                'Invoice' => config('cache-config.tags.invoices'),
                'Payment' => config('cache-config.tags.payments'),
                default => null,
            };

            if ($tag && config('cache-config.enable_tagging')) {
                Cache::tags([$tag, "tenant:{$tenantId}"])->flush();
            }
        } catch (\Exception $e) {
            Log::error('Invalidate model cache failed', [
                'model' => $model,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get current tenant ID from authenticated user.
     */
    private function getCurrentTenantId(): int
    {
        $user = auth()->user();

        return $user ? $user->tenant_id : 0;
    }

    /**
     * Generate tenant-specific cache key.
     */
    private function getTenantCacheKey(int $tenantId, string $key): string
    {
        $prefix = config('cache-config.prefixes.tenant');

        return "{$prefix}:{$tenantId}:{$key}";
    }

    /**
     * Generate stats cache key.
     */
    private function getStatsCacheKey(int $tenantId, string $key): string
    {
        $prefix = config('cache-config.prefixes.stats');

        return "{$prefix}:{$tenantId}:{$key}";
    }

    /**
     * Generate dashboard cache key.
     */
    private function getDashboardCacheKey(int $tenantId): string
    {
        $prefix = config('cache-config.prefixes.dashboard');

        return "{$prefix}:{$tenantId}:stats";
    }

    /**
     * Generate packages cache key.
     */
    private function getPackagesCacheKey(int $tenantId): string
    {
        $prefix = config('cache-config.prefixes.package');

        return "{$prefix}:{$tenantId}:list";
    }

    /**
     * Generate gateways cache key.
     */
    private function getGatewaysCacheKey(int $tenantId): string
    {
        $prefix = config('cache-config.prefixes.gateway');

        return "{$prefix}:{$tenantId}:list";
    }

    /**
     * Generate permissions cache key.
     */
    private function getPermissionsCacheKey(int $userId): string
    {
        $prefix = config('cache-config.prefixes.permission');

        return "{$prefix}:{$userId}:list";
    }

    /**
     * Generate device status cache key.
     */
    private function getDeviceStatusCacheKey(int $tenantId, int $deviceId): string
    {
        $prefix = config('cache-config.prefixes.device');

        return "{$prefix}:{$tenantId}:{$deviceId}:status";
    }

    /**
     * Get cache keys by pattern (Redis-specific).
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        try {
            $store = Cache::getStore();
            // This works with Redis driver only
            if ($store instanceof \Illuminate\Cache\RedisStore) {
                /** @var \Illuminate\Redis\Connections\Connection $connection */
                $connection = $store->connection();

                return $connection->keys($pattern) ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Get cache keys by pattern failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
