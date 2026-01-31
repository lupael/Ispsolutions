<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NetworkUserSession Model
 * 
 * Represents customer session data from the 'customer_sessions' table.
 * 
 * Migration History:
 * - network_user_sessions table → customer_sessions table (2026-01-30)
 * 
 * Note: The table was renamed from 'network_user_sessions' to 'customer_sessions'
 * to align with the new terminology, but this model name is preserved for backward
 * compatibility. This is the standard model for accessing customer session data.
 */
class NetworkUserSession extends Model
{
    /**
     * The table associated with the model.
     * 
     * NOTE: Points to 'customer_sessions' table for backward compatibility.
     * Original 'network_user_sessions' table was renamed to 'customer_sessions'.
     *
     * @var string
     */
    protected $table = 'customer_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'start_time',
        'end_time',
        'upload_bytes',
        'download_bytes',
        'ip_address',
        'mac_address',
        'nas_ip',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'upload_bytes' => 'integer',
        'download_bytes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(NetworkUser::class, 'user_id');
    }
}
