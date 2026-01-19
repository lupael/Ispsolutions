<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CableTvSubscription extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'package_id',
        'subscriber_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'installation_address',
        'start_date',
        'expiry_date',
        'status',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'auto_renew' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CableTvPackage::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function daysRemaining(): int
    {
        // diffInDays with false returns negative for past dates
        // We use max(0, ...) to ensure non-negative return value
        return max(0, $this->expiry_date->diffInDays(now(), false));
    }
}
