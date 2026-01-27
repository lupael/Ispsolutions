<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSpeedLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'upload_speed',
        'download_speed',
        'is_temporary',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'upload_speed' => 'integer',
        'download_speed' => 'integer',
        'is_temporary' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if speed limit has expired.
     */
    public function isExpired(): bool
    {
        return $this->is_temporary 
            && $this->expires_at 
            && $this->expires_at->isPast();
    }

    /**
     * Scope for active (non-expired) speed limits.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_temporary', false)
              ->orWhere(function ($q2) {
                  $q2->where('is_temporary', true)
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '>', now());
              });
        });
    }
}
