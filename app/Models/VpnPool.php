<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VpnPool extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'network',
        'subnet_mask',
        'start_ip',
        'end_ip',
        'gateway',
        'dns_primary',
        'dns_secondary',
        'is_active',
        'protocol', // pptp, l2tp, openvpn, ikev2
        'total_ips',
        'used_ips',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_ips' => 'integer',
        'used_ips' => 'integer',
    ];

    public function vpnAccounts()
    {
        return $this->hasMany(MikrotikVpnAccount::class, 'pool_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableIpsAttribute()
    {
        return $this->total_ips - $this->used_ips;
    }

    public function getUsagePercentageAttribute()
    {
        return $this->total_ips > 0 
            ? round(($this->used_ips / $this->total_ips) * 100, 2) 
            : 0;
    }
}
