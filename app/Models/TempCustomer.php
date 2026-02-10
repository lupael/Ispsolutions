<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TempCustomer extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'session_id',
        'step',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
        'step' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically set expiry time on creation
        static::creating(function ($model) {
            if (!$model->expires_at) {
                $model->expires_at = now()->addHours(24);
            }

            // Automatically set tenant_id from the authenticated user if not already set
            if (Auth::check() && !$model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    /**
     * Get the user that owns the temp customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter non-expired records.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to filter expired records.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Get data for a specific step.
     */
    public function getStepData(int $step): array
    {
        return $this->data["step_{$step}"] ?? [];
    }

    /**
     * Set data for a specific step.
     */
    public function setStepData(int $step, array $data): void
    {
        $currentData = $this->data ?? [];
        $currentData["step_{$step}"] = $data;
        $this->data = $currentData;
        $this->step = $step;
    }

    /**
     * Get all collected data across all steps.
     */
    public function getAllData(): array
    {
        $allData = [];
        foreach ($this->data ?? [] as $key => $value) {
            if (str_starts_with($key, 'step_')) {
                $allData = array_merge($allData, $value);
            }
        }
        return $allData;
    }

    /**
     * Check if the temp customer has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Extend the expiration time.
     */
    public function extend(int $hours = 24): void
    {
        $this->expires_at = now()->addHours($hours);
        $this->save();
    }

    /**
     * Delete expired temp customers.
     */
    public static function deleteExpired(): int
    {
        return static::expired()->delete();
    }
}
