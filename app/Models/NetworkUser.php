<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'service_type',
        'package_id',
        'status',
        'is_active',
        'user_id',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ipAllocations(): HasMany
    {
        return $this->hasMany(IpAllocation::class, 'username', 'username');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(NetworkUserSession::class, 'user_id');
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    // Query scopes for optimization with indexed filters
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByServiceType(Builder $query, string $serviceType): Builder
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeWithPackageAndInvoices(Builder $query): Builder
    {
        // Optimized: Eager load relationships to avoid N+1 queries
        return $query->with(['package:id,name,price,bandwidth_upload,bandwidth_download']);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
