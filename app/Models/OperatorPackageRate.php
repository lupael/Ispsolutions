<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorPackageRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'package_id',
        'custom_price',
        'commission_percentage',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
