<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpAllocationHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ip_allocation_id',
        'user_id',
        'ip_address',
        'action',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(IpAllocation::class, 'ip_allocation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
