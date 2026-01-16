<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class IpAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_subnet_id',
        'ip_address',
        'user_id',
        'allocation_type',
        'status',
        'allocated_at',
        'released_at',
        'expires_at',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'released_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function subnet(): BelongsTo
    {
        return $this->belongsTo(IpSubnet::class, 'ip_subnet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(IpAllocationHistory::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now())
                     ->where('status', 'active');
    }
}
