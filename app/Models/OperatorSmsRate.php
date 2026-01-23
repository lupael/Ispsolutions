<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorSmsRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'rate_per_sms',
        'bulk_rate_threshold',
        'bulk_rate_per_sms',
    ];

    protected $casts = [
        'rate_per_sms' => 'decimal:4',
        'bulk_rate_per_sms' => 'decimal:4',
        'bulk_rate_threshold' => 'integer',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function calculateCost(int $smsCount): float
    {
        if ($smsCount >= $this->bulk_rate_threshold && $this->bulk_rate_per_sms) {
            return $smsCount * (float) $this->bulk_rate_per_sms;
        }

        return $smsCount * (float) $this->rate_per_sms;
    }
}
