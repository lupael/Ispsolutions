<?php

declare(strict_types=1);

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

    /**
     * Calculate the total number of IPs in this pool
     */
    public function getTotalIpsAttribute(): int
    {
        if (empty($this->start_ip) || empty($this->end_ip)) {
            return 0;
        }

        // Validate IP addresses
        $startLong = ip2long($this->start_ip);
        $endLong = ip2long($this->end_ip);
        
        if ($startLong === false || $endLong === false) {
            return 0;
        }

        // Invalid range: start IP is greater than end IP
        if ($startLong > $endLong) {
            return 0;
        }

        return (int) ($endLong - $startLong) + 1;
    }

    /**
     * Calculate the number of used IPs
     */
    public function getUsedIpsAttribute(): int
    {
        // Count allocated IPs from subnets or allocations
        return $this->subnets()->count();
    }

    /**
     * Get utilization percentage
     */
    public function utilizationPercent(): float
    {
        $total = $this->total_ips;
        if ($total === 0) {
            return 0;
        }

        return round(($this->used_ips / $total) * 100, 1);
    }

    /**
     * Get CSS class for utilization display
     */
    public function utilizationClass(): string
    {
        $percent = $this->utilizationPercent();

        return match (true) {
            $percent >= 90 => 'bg-danger',
            $percent >= 70 => 'bg-warning',
            default => 'bg-success',
        };
    }
}
