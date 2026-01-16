<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpSubnet extends Model
{
    protected $fillable = [
        'pool_id',
        'network',
        'prefix_length',
        'gateway',
        'vlan_id',
        'status',
    ];

    protected $casts = [
        'pool_id' => 'integer',
        'prefix_length' => 'integer',
        'vlan_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'pool_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(IpAllocation::class, 'subnet_id');
    }
}
