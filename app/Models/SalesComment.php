<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesComment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'lead_id',
        'customer_id',
        'type',
        'subject',
        'comment',
        'contact_date',
        'next_action',
        'next_action_date',
        'attachment_path',
        'is_private',
    ];

    protected $casts = [
        'contact_date' => 'datetime',
        'next_action_date' => 'datetime',
        'is_private' => 'boolean',
    ];

    /**
     * Comment type constants
     */
    public const TYPE_NOTE = 'note';

    public const TYPE_CALL = 'call';

    public const TYPE_MEETING = 'meeting';

    public const TYPE_EMAIL = 'email';

    public const TYPE_FOLLOW_UP = 'follow_up';

    /**
     * Get all available comment types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_NOTE,
            self::TYPE_CALL,
            self::TYPE_MEETING,
            self::TYPE_EMAIL,
            self::TYPE_FOLLOW_UP,
        ];
    }

    /**
     * Get the user who created this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lead associated with this comment
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the customer associated with this comment
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter public comments
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope to filter private comments
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('contact_date', [$startDate, $endDate]);
    }
}
