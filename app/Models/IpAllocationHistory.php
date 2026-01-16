<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpAllocationHistory extends Model
{
    protected $table = 'ip_allocation_history';

    protected $fillable = [
        'allocation_id',
        'ip_address',
        'mac_address',
        'username',
        'action',
        'allocated_at',
        'released_at',
    ];

    protected $casts = [
        'allocation_id' => 'integer',
        'allocated_at' => 'datetime',
        'released_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(IpAllocation::class, 'allocation_id');
    }
}
