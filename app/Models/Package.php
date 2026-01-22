<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'price',
        'bandwidth_upload',
        'bandwidth_download',
        'validity_days',
        'billing_type',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'bandwidth_upload' => 'integer',
        'bandwidth_download' => 'integer',
        'validity_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class, 'package_id');
    }

    // Query scopes for optimization
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
