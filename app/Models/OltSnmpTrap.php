<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OltSnmpTrap Model
 *
 * Represents a single SNMP trap message received from an OLT device.
 *
 * @property int $id
 * @property int|null $olt_id
 * @property int|null $tenant_id
 * @property string|null $source_ip
 * @property string|null $trap_type
 * @property string|null $oid
 * @property string $severity
 * @property string|null $message
 * @property array|null $trap_data
 * @property bool $is_acknowledged
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Olt|null $olt
 * @property-read \App\Models\User|null $acknowledgedByUser
 */
class OltSnmpTrap extends Model
{
    use BelongsToTenant;

    /**
     * Severity level constants for SNMP traps.
     */
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_AVERAGE = 'average';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_DISASTER = 'disaster';

    /**
     * Get all available severity levels.
     *
     * @return string[]
     */
    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_INFO,
            self::SEVERITY_WARNING,
            self::SEVERITY_AVERAGE,
            self::SEVERITY_HIGH,
            self::SEVERITY_CRITICAL,
            self::SEVERITY_DISASTER,
        ];
    }

    protected $fillable = [
        'olt_id',
        'tenant_id',
        'source_ip',
        'trap_type',
        'oid',
        'severity',
        'message',
        'trap_data',
        'is_acknowledged',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'trap_data' => 'array',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the OLT that generated the trap.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get the user who acknowledged the trap.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Scope a query to only include unacknowledged traps.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope a query to only include acknowledged traps.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('is_acknowledged', true);
    }

    /**
     * Scope a query to filter traps by severity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $severity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Mark the trap as acknowledged by a user.
     *
     * @param int $userId The ID of the user acknowledging the trap.
     * @return bool True on success, false on failure.
     */
    public function acknowledge(int $userId): bool
    {
        return $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }
}
