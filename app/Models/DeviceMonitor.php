<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Device Monitor Model
 *
 * Stores real-time monitoring data for network devices.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $monitorable_type
 * @property int $monitorable_id
 * @property string $status
 * @property float|null $cpu_usage
 * @property float|null $memory_usage
 * @property int|null $uptime
 * @property \Illuminate\Support\Carbon|null $last_check_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DeviceMonitor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'operator_id',
        'monitorable_type',
        'monitorable_id',
        'status',
        'cpu_usage',
        'memory_usage',
        'uptime',
        'last_check_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'uptime' => 'integer',
        'last_check_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent monitorable model (MikrotikRouter, Olt, or Onu).
     */
    public function monitorable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tenant that owns the monitor.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the operator for this device monitor
     * Task 10.2: Add operator() relationship
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Scope a query to only include online devices.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope a query to only include offline devices.
     */
    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    /**
     * Scope a query to only include degraded devices.
     */
    public function scopeDegraded($query)
    {
        return $query->where('status', 'degraded');
    }

    /**
     * Scope a query to filter by device type.
     */
    public function scopeDeviceType($query, string $type)
    {
        return $query->where('monitorable_type', $type);
    }

    /**
     * Check if the device is online.
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Check if the device is offline.
     */
    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    /**
     * Get uptime in human-readable format.
     */
    public function getUptimeHuman(): ?string
    {
        if (! $this->uptime) {
            return null;
        }

        $days = floor($this->uptime / 86400);
        $hours = floor(($this->uptime % 86400) / 3600);
        $minutes = floor(($this->uptime % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0 || empty($parts)) {
            $parts[] = $minutes . 'm';
        }

        return implode(' ', $parts);
    }

    /**
     * Scope a query by operator
     * Task 10.3: Add device monitoring delegation
     */
    public function scopeByOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Scope a query to only include devices monitored by a specific operator or unassigned
     * Task 10.3: Add device monitoring delegation
     */
    public function scopeForOperator($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('operator_id', $userId)
              ->orWhereNull('operator_id');
        });
    }
}
