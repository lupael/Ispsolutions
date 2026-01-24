<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterProvisioningLog extends Model
{
    protected $fillable = [
        'router_id',
        'user_id',
        'template_id',
        'action',
        'status',
        'error_message',
        'configuration',
        'steps',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'steps' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RouterConfigurationTemplate::class, 'template_id');
    }
}
