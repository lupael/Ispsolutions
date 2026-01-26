<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingProfile extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'billing_day',
        'billing_time',
        'timezone',
        'currency',
        'auto_generate_bill',
        'auto_suspend',
        'grace_period_days',
        'is_active',
    ];

    protected $casts = [
        'billing_day' => 'integer',
        'auto_generate_bill' => 'boolean',
        'auto_suspend' => 'boolean',
        'grace_period_days' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns this billing profile.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all users assigned to this billing profile.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope to filter active profiles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by profile type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted billing schedule description.
     */
    public function getScheduleDescriptionAttribute(): string
    {
        return match ($this->type) {
            'daily' => 'Daily billing at ' . ($this->billing_time ?? '00:00'),
            'monthly' => 'Monthly billing on day ' . ($this->billing_day ?? '1'),
            'free' => 'Free - No billing',
            default => 'Unknown billing type',
        };
    }

    /**
     * Get badge color for profile type.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'daily' => 'blue',
            'monthly' => 'green',
            'free' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if profile can be deleted.
     */
    public function canDelete(): bool
    {
        return $this->users()->count() === 0;
    }
}
