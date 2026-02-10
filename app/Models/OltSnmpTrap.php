<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get the user who acknowledged the trap.
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
