<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'nas_id',
        'status',
        'total_count',
        'success_count',
        'failed_count',
        'options',
        'errors',
        'completed_at',
    ];

    protected $casts = [
        'total_count' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'options' => 'array',
        'errors' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the operator.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the NAS device.
     */
    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class);
    }

    /**
     * Check if import is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if import is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if import failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_count === 0) {
            return 0;
        }

        $processed = $this->success_count + $this->failed_count;
        return round(($processed / $this->total_count) * 100, 2);
    }
}
