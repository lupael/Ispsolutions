<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'cost_type',
        'amount',
        'description',
        'cost_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cost_date' => 'date',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeNttn($query)
    {
        return $query->where('cost_type', 'nttn');
    }

    public function scopeBandwidth($query)
    {
        return $query->where('cost_type', 'bandwidth');
    }
}
