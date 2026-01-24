<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTimeLimit extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'daily_minutes_limit',
        'monthly_minutes_limit',
        'session_duration_limit',
        'current_day_minutes',
        'current_month_minutes',
        'allowed_start_time',
        'allowed_end_time',
        'auto_disconnect_on_limit',
        'day_reset_date',
        'month_reset_date',
    ];

    protected $casts = [
        'daily_minutes_limit' => 'integer',
        'monthly_minutes_limit' => 'integer',
        'session_duration_limit' => 'integer',
        'current_day_minutes' => 'integer',
        'current_month_minutes' => 'integer',
        'auto_disconnect_on_limit' => 'boolean',
        'day_reset_date' => 'date',
        'month_reset_date' => 'date',
    ];

    /**
     * Get the user that owns this time limit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if daily time limit is exceeded
     */
    public function isDailyLimitExceeded(): bool
    {
        if (!$this->daily_minutes_limit) {
            return false;
        }
        
        return $this->current_day_minutes >= $this->daily_minutes_limit;
    }

    /**
     * Check if monthly time limit is exceeded
     */
    public function isMonthlyLimitExceeded(): bool
    {
        if (!$this->monthly_minutes_limit) {
            return false;
        }
        
        return $this->current_month_minutes >= $this->monthly_minutes_limit;
    }

    /**
     * Get remaining daily minutes
     */
    public function remainingDailyMinutes(): int
    {
        if (!$this->daily_minutes_limit) {
            return 0;
        }
        
        return max(0, $this->daily_minutes_limit - $this->current_day_minutes);
    }

    /**
     * Get remaining monthly minutes
     */
    public function remainingMonthlyMinutes(): int
    {
        if (!$this->monthly_minutes_limit) {
            return 0;
        }
        
        return max(0, $this->monthly_minutes_limit - $this->current_month_minutes);
    }

    /**
     * Check if current time is within allowed access hours
     */
    public function isWithinAllowedTime(): bool
    {
        if (!$this->allowed_start_time || !$this->allowed_end_time) {
            return true;
        }
        
        $now = now()->format('H:i:s');
        return $now >= $this->allowed_start_time && $now <= $this->allowed_end_time;
    }
}
