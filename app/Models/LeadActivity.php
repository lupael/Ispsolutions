<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'user_id',
        'type',
        'title',
        'description',
        'activity_date',
        'duration_minutes',
        'outcome',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Activity type constants
     */
    public const TYPE_CALL = 'call';

    public const TYPE_EMAIL = 'email';

    public const TYPE_MEETING = 'meeting';

    public const TYPE_NOTE = 'note';

    public const TYPE_TASK = 'task';

    public const TYPE_STATUS_CHANGE = 'status_change';

    /**
     * Get all available activity types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CALL,
            self::TYPE_EMAIL,
            self::TYPE_MEETING,
            self::TYPE_NOTE,
            self::TYPE_TASK,
            self::TYPE_STATUS_CHANGE,
        ];
    }

    /**
     * Get the lead this activity belongs to
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }
}
