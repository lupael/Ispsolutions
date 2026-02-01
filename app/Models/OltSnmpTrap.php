<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltSnmpTrap extends Model
{
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function acknowledge(int $userId): void
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }
}
