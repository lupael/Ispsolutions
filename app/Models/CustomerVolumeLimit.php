<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerVolumeLimit extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'monthly_limit_mb',
        'daily_limit_mb',
        'current_month_usage_mb',
        'current_day_usage_mb',
        'month_reset_date',
        'day_reset_date',
        'auto_suspend_on_limit',
        'rollover_enabled',
        'rollover_balance_mb',
    ];

    protected $casts = [
        'monthly_limit_mb' => 'integer',
        'daily_limit_mb' => 'integer',
        'current_month_usage_mb' => 'integer',
        'current_day_usage_mb' => 'integer',
        'month_reset_date' => 'date',
        'day_reset_date' => 'date',
        'auto_suspend_on_limit' => 'boolean',
        'rollover_enabled' => 'boolean',
        'rollover_balance_mb' => 'integer',
    ];

    /**
     * Get the user that owns this volume limit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if monthly limit is exceeded
     */
    public function isMonthlyLimitExceeded(): bool
    {
        if (!$this->monthly_limit_mb) {
            return false;
        }
        
        return $this->current_month_usage_mb >= $this->monthly_limit_mb;
    }

    /**
     * Check if daily limit is exceeded
     */
    public function isDailyLimitExceeded(): bool
    {
        if (!$this->daily_limit_mb) {
            return false;
        }
        
        return $this->current_day_usage_mb >= $this->daily_limit_mb;
    }

    /**
     * Get remaining monthly data in MB
     */
    public function remainingMonthlyMb(): int
    {
        if (!$this->monthly_limit_mb) {
            return 0;
        }
        
        return max(0, $this->monthly_limit_mb - $this->current_month_usage_mb);
    }

    /**
     * Get remaining daily data in MB
     */
    public function remainingDailyMb(): int
    {
        if (!$this->daily_limit_mb) {
            return 0;
        }
        
        return max(0, $this->daily_limit_mb - $this->current_day_usage_mb);
    }
}
