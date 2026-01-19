<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status', // trial, active, suspended, expired, cancelled
        'start_date',
        'end_date',
        'trial_ends_at',
        'amount',
        'currency',
        'notes',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trial_ends_at' => 'date',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere('end_date', '<', now());
        });
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               ($this->end_date === null || $this->end_date->isToday() || $this->end_date->isFuture());
    }

    public function isOnTrial()
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
