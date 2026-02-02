<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Olt extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'brand',
        'ip_address',
        'port',                 // SSH/Telnet port
        'management_protocol',  // ssh, telnet, snmp
        'username',
        'password',
        'snmp_community',
        'snmp_version',
        'snmp_port',
        'model',
        'firmware_version',
        'location',
        'coverage_area',
        'total_ports',
        'max_onus',
        'status',
        'health_status',
        'last_backup_at',
        'last_health_check_at',
    ];

    protected $hidden = [
        'password',
        'username',
        'snmp_community',
    ];

    protected $casts = [
        'port' => 'integer',
        'snmp_port' => 'integer',
        'username' => 'encrypted',
        'password' => 'encrypted',
        'snmp_community' => 'encrypted',
        'last_backup_at' => 'datetime',
        'last_health_check_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(OltBackup::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canConnect(): bool
    {
        // Allow SNMP-only devices (no username/password), or SSH/Telnet requiring credentials
        if ($this->management_protocol === 'snmp') {
            return $this->isActive() && ! empty($this->ip_address);
        }

        return $this->isActive() && ! empty($this->ip_address) && ! empty($this->username) && ! empty($this->password);
    }
}