<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'hotspot_user_id',
        'customer_id',
        'username',
        'mac_address',
        'ip_address',
        'session_id',
        'login_type',
        'scenario',
        'login_at',
        'logout_at',
        'session_duration',
        'device_fingerprint',
        'user_agent',
        'nas_ip_address',
        'calling_station_id',
        'link_token',
        'link_expires_at',
        'is_link_login',
        'home_operator_id',
        'federated_login',
        'redirect_url',
        'status',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'link_expires_at' => 'datetime',
        'session_duration' => 'integer',
        'is_link_login' => 'boolean',
        'federated_login' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Login types
     */
    public const TYPE_NORMAL = 'normal';
    public const TYPE_LINK = 'link';
    public const TYPE_FEDERATED = 'federated';
    public const TYPE_OTP = 'otp';

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(NetworkUser::class, 'customer_id');
    }

    /**
     * @deprecated Use customer() instead
     */
    public function networkUser(): BelongsTo
    {
        return $this->belongsTo(NetworkUser::class, 'customer_id');
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->logout_at === null;
    }

    /**
     * Check if link login is expired
     */
    public function isLinkExpired(): bool
    {
        return $this->is_link_login && 
               $this->link_expires_at && 
               $this->link_expires_at->isPast();
    }

    /**
     * Mark session as logged out
     */
    public function markAsLoggedOut(): void
    {
        $duration = $this->login_at ? now()->diffInSeconds($this->login_at) : 0;
        
        $this->update([
            'logout_at' => now(),
            'session_duration' => $duration,
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    /**
     * Mark session as failed
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Scope: Active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereNull('logout_at');
    }

    /**
     * Scope: By tenant
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Link logins
     */
    public function scopeLinkLogins($query)
    {
        return $query->where('is_link_login', true);
    }

    /**
     * Scope: Federated logins
     */
    public function scopeFederatedLogins($query)
    {
        return $query->where('federated_login', true);
    }
}
