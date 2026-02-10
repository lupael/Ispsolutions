<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\OltVendorDetector;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * OLT (Optical Line Terminal) Model
 *
 * Represents an OLT device that manages multiple ONUs.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $ip_address
 * @property int $port
 * @property string $management_protocol
 * @property string $username
 * @property string $password
 * @property string|null $snmp_community
 * @property string|null $snmp_version
 * @property string|null $model
 * @property string|null $location
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_backup_at
 * @property \Illuminate\Support\Carbon|null $last_health_check_at
 * @property string $health_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Olt extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'brand',
        'ip_address',
        'port',
        'telnet_port',
        'management_protocol',
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'username',
        'snmp_community',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'port' => 'integer',
        'username' => 'encrypted',
        'password' => 'encrypted',
        'snmp_community' => 'encrypted',
        'last_backup_at' => 'datetime',
        'last_health_check_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the ONUs for the OLT.
     */
    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    /**
     * Get the backups for the OLT.
     */
    public function backups(): HasMany
    {
        return $this->hasMany(OltBackup::class);
    }

    /**
     * Scope a query to only include active OLTs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive OLTs.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include OLTs in maintenance mode.
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Check if the OLT is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the OLT can be connected to.
     */
    public function canConnect(): bool
    {
        return $this->isActive() && ! empty($this->ip_address) && ! empty($this->username) && ! empty($this->password);
    }

    /**
     * Get the detected vendor for this OLT.
     */
    public function getVendorAttribute(): string
    {
        return OltVendorDetector::detect($this);
    }

    /**
     * Get the correct management port based on protocol.
     */
    public function getPortAttribute($value): int
    {
        // If telnet is used and telnet_port is set, prefer it
        if ($this->management_protocol === 'telnet' && !empty($this->telnet_port)) {
            return (int) $this->telnet_port;
        }

        // Fallback to the default port
        return (int) $value;
    }
}
