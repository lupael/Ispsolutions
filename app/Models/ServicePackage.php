<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'bandwidth_up',
        'bandwidth_down',
        'price',
        'billing_cycle',
        'is_active',
    ];

    protected $casts = [
        'bandwidth_up' => 'integer',
        'bandwidth_down' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
