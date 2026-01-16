<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpAllocation extends Model
{
    protected $fillable = [
        'subnet_id',
        'ip_address',
        'mac_address',
        'username',
        'allocated_at',
        'released_at',
        'status',
    ];

    protected $casts = [
        'subnet_id' => 'integer',
        'allocated_at' => 'datetime',
        'released_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function subnet(): BelongsTo
    {
        return $this->belongsTo(IpSubnet::class, 'subnet_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(IpAllocationHistory::class, 'allocation_id');
    }
}
