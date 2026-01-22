<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ONU (Optical Network Unit) Model
 *
 * Represents an ONU device connected to an OLT.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $olt_id
 * @property string $pon_port
 * @property int $onu_id
 * @property string $serial_number
 * @property string|null $mac_address
 * @property int|null $network_user_id
 * @property string|null $name
 * @property string|null $description
 * @property string $status
 * @property float|null $signal_rx
 * @property float|null $signal_tx
 * @property int|null $distance
 * @property string|null $ipaddress
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property \Illuminate\Support\Carbon|null $last_sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Onu extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'olt_id',
        'pon_port',
        'onu_id',
        'serial_number',
        'mac_address',
        'network_user_id',
        'name',
        'description',
        'status',
        'signal_rx',
        'signal_tx',
        'distance',
        'ipaddress',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'olt_id' => 'integer',
        'onu_id' => 'integer',
        'network_user_id' => 'integer',
        'signal_rx' => 'decimal:2',
        'signal_tx' => 'decimal:2',
        'distance' => 'integer',
        'last_seen_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the ONU.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the OLT that owns the ONU.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get the network user associated with the ONU.
     */
    public function networkUser(): BelongsTo
    {
        return $this->belongsTo(NetworkUser::class);
    }

    /**
     * Scope a query to only include online ONUs.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope a query to only include offline ONUs.
     */
    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    /**
     * Scope a query to filter ONUs by OLT.
     */
    public function scopeByOlt($query, int $oltId)
    {
        return $query->where('olt_id', $oltId);
    }

    /**
     * Check if the ONU is online.
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Get the full PON path for the ONU.
     *
     * @return string Format: "OLT-Name / PON Port / ONU ID"
     */
    public function getFullPonPath(): string
    {
        // Only try to load the relationship if the olt_id is set and not already loaded
        if ($this->olt_id && ! $this->relationLoaded('olt')) {
            $this->load('olt');
        }

        $oltName = $this->olt?->name ?? 'Unknown OLT';

        return "{$oltName} / {$this->pon_port} / {$this->onu_id}";
    }
}
