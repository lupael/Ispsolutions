<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id',
        'operator_id',
        'name',
        'description',
        'price',
        'bandwidth_upload',
        'bandwidth_download',
        'validity_days',
        'billing_type',
        'status',
        'is_global',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'bandwidth_upload' => 'integer',
        'bandwidth_download' => 'integer',
        'validity_days' => 'integer',
        'is_global' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class, 'package_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function profileMappings(): HasMany
    {
        return $this->hasMany(PackageProfileMapping::class, 'package_id');
    }

    public function operatorRates(): HasMany
    {
        return $this->hasMany(OperatorPackageRate::class, 'package_id');
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

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    public function scopeForOperator(Builder $query, int $operatorId): Builder
    {
        return $query->where(function ($q) use ($operatorId) {
            $q->where('is_global', true)
              ->orWhere('operator_id', $operatorId);
        });
    }
}
