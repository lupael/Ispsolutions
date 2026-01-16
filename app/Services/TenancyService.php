<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenancyService
{
    private const CACHE_KEY = 'current_tenant';

    private const CACHE_TTL = 3600; // 1 hour

    private ?Tenant $currentTenant = null;

    private bool $initialized = false;

    /**
     * Set the current tenant.
     */
    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
        $this->initialized = true;

        if ($tenant) {
            Cache::put(
                $this->getCacheKey(),
                $tenant->id,
                now()->addSeconds(self::CACHE_TTL)
            );
        } else {
            Cache::forget($this->getCacheKey());
        }
    }

    /**
     * Get the current tenant.
     */
    public function getCurrentTenant(): ?Tenant
    {
        if (! $this->initialized) {
            $this->loadTenantFromCache();
        }

        return $this->currentTenant;
    }

    /**
     * Get the current tenant ID.
     */
    public function getCurrentTenantId(): ?int
    {
        $tenant = $this->getCurrentTenant();

        return $tenant?->id;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function hasTenant(): bool
    {
        return $this->getCurrentTenant() !== null;
    }

    /**
     * Resolve tenant by domain or subdomain.
     */
    public function resolveTenantByDomain(string $host): ?Tenant
    {
        // Try exact domain match first
        $tenant = Tenant::where('domain', $host)
            ->where('status', 'active')
            ->first();

        if ($tenant) {
            return $tenant;
        }

        // Try subdomain match
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            $subdomain = $parts[0];
            $tenant = Tenant::where('subdomain', $subdomain)
                ->where('status', 'active')
                ->first();
        }

        return $tenant;
    }

    /**
     * Execute a callback in the context of a specific tenant.
     */
    public function runForTenant(?Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->currentTenant;

        try {
            $this->setCurrentTenant($tenant);

            return $callback();
        } finally {
            $this->setCurrentTenant($previousTenant);
        }
    }

    /**
     * Forget the current tenant.
     */
    public function forgetTenant(): void
    {
        $this->setCurrentTenant(null);
    }

    /**
     * Load tenant from cache.
     */
    private function loadTenantFromCache(): void
    {
        $tenantId = Cache::get($this->getCacheKey());

        if ($tenantId) {
            $this->currentTenant = Tenant::find($tenantId);
        }

        $this->initialized = true;
    }

    /**
     * Get the cache key for the current request.
     */
    private function getCacheKey(): string
    {
        return self::CACHE_KEY . '_' . request()->ip();
    }
}
