<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'pool_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subnets(): HasMany
    {
        return $this->hasMany(IpSubnet::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasManyThrough(IpAllocation::class, IpSubnet::class);
    }
}
