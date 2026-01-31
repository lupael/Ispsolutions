<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NetworkUserSession Model
 * 
 * @deprecated This model is maintained for backward compatibility only.
 *             The 'network_user_sessions' table has been renamed to 'customer_sessions'.
 *             Consider using CustomerSession model for new code.
 * 
 * Migration History:
 * - network_user_sessions table → customer_sessions table (2026-01-30)
 * 
 * This model provides transparent backward compatibility by pointing to the
 * 'customer_sessions' table. All existing code will continue to work.
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
