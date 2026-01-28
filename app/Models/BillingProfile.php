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

    /**
     * Get due date with ordinal suffix
     * Task 2.1: Add ordinal suffix to billing due dates
     */
    public function getDueDateWithOrdinal(): string
    {
        $day = $this->billing_day ?? 1;
        $suffix = $this->getOrdinalSuffix($day);
        
        return "{$day}{$suffix} day";
    }

    /**
     * Get ordinal suffix for a number
     */
    private function getOrdinalSuffix(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        // Handle special cases (11th, 12th, 13th)
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return 'th';
        }
        
        return match ($lastDigit) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    /**
     * Get due date figure attribute
     * Task 2.2: Add due_date_figure computed attribute
     */
    public function getDueDateFigureAttribute(): string
    {
        if ($this->type === 'monthly') {
            return $this->getDueDateWithOrdinal() . ' of each month';
        }
        
        if ($this->type === 'daily') {
            return 'Daily at ' . ($this->billing_time ?? '00:00');
        }
        
        return 'No billing';
    }

    /**
     * Enhanced grace period calculation
     * Task 2.4: Enhance grace period calculation
     */
    public function gracePeriod(): int
    {
        return max($this->grace_period_days ?? 0, 0);
    }
}
