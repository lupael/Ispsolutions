<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpPool extends Model
{
    protected $fillable = [
        'name',
        'description',
        'pool_type',
        'start_ip',
        'end_ip',
        'gateway',
        'dns_servers',
        'vlan_id',
        'status',
    ];

    protected $casts = [
        'vlan_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function subnets(): HasMany
    {
        return $this->hasMany(IpSubnet::class, 'pool_id');
    }
}
