<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialPermission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'permission_key',
        'resource_type',
        'resource_id',
        'description',
        'granted_at',
        'expires_at',
        'granted_by',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who has this permission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who granted this permission
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if the permission is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the permission is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope to get only active permissions
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope to get expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope to filter by permission key
     */
    public function scopeByPermission($query, string $permissionKey)
    {
        return $query->where('permission_key', $permissionKey);
    }

    /**
     * Scope to filter by resource
     */
    public function scopeByResource($query, string $resourceType, ?int $resourceId = null)
    {
        $query->where('resource_type', $resourceType);
        
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        
        return $query;
    }
}
