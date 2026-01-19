<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CableTvPackage extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'monthly_price',
        'setup_fee',
        'validity_days',
        'max_devices',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'validity_days' => 'integer',
        'max_devices' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(CableTvChannel::class, 'cable_tv_channel_package');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CableTvSubscription::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
