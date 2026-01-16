<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Services\TenancyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait for a model.
     */
    protected static function bootBelongsToTenant(): void
    {
        // Automatically set tenant_id when creating
        static::creating(function ($model) {
            if (! $model->tenant_id) {
                $tenancyService = app(TenancyService::class);
                $currentTenant = $tenancyService->getCurrentTenant();
                
                if ($currentTenant) {
                    $model->tenant_id = $currentTenant->id;
                }
            }
        });

        // Global scope to filter by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenancyService = app(TenancyService::class);
            $currentTenant = $tenancyService->getCurrentTenant();

            if ($currentTenant) {
                $builder->where('tenant_id', $currentTenant->id);
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include models for a specific tenant.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to include all tenants (bypass global scope).
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
