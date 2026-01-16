<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkUserSession extends Model
{
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
