<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * NAS (Network Access Server) Model
 *
 * Represents a NAS device, typically a router, that authenticates users.
 * This model corresponds to the 'nas' table used by FreeRADIUS.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name Human-readable name for the NAS.
 * @property string $nas_name The identifier used by RADIUS (e.g., IP address or hostname).
 * @property string|null $short_name
 * @property string|null $type
 * @property int|null $ports
 * @property string $secret The RADIUS shared secret.
 * @property string|null $server
 * @property string|null $community SNMP community string.
 * @property string|null $description
 * @property string|null $status
 */
class Nas extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'nas';

    public const STATUS_ACTIVE = 'active';
    protected $fillable = [
        'tenant_id',
        'name',
        'nas_name',
        'short_name',
        'type',
        'ports',
        'secret',
        'server',
        'community',
        'description',
        'status',
    ];

    protected $hidden = [
        'secret',
        'community',
    ];

    protected $casts = [
        'ports' => 'integer',
        'secret' => 'encrypted',
        'community' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the MikroTik routers associated with this NAS.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mikrotikRouters(): HasMany
    {
        return $this->hasMany(MikrotikRouter::class, 'nas_id');
    }

    /**
     * Scope a query to only include active NAS devices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include inactive NAS devices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', '!=', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
