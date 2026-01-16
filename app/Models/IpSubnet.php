<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpSubnet extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_pool_id',
        'network',
        'prefix_length',
        'gateway',
        'vlan_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'prefix_length' => 'integer',
        'vlan_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'ip_pool_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(IpAllocation::class);
    }
}
