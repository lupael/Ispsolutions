<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'nas_ip_address',
        'nas_port',
        'acct_session_id',
        'acct_start_time',
        'acct_stop_time',
        'acct_session_time',
        'acct_input_octets',
        'acct_output_octets',
        'acct_terminate_cause',
    ];

    protected $casts = [
        'acct_start_time' => 'datetime',
        'acct_stop_time' => 'datetime',
        'acct_session_time' => 'integer',
        'acct_input_octets' => 'integer',
        'acct_output_octets' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
