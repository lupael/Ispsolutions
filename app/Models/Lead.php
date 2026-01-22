<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'status',
        'source',
        'assigned_to',
        'estimated_value',
        'probability',
        'expected_close_date',
        'notes',
        'last_contact_date',
        'next_follow_up_date',
        'converted_to_customer_id',
        'converted_at',
        'lost_reason',
        'created_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'last_contact_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
        'converted_at' => 'datetime',
    ];

    /**
     * Lead status constants
     */
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_PROPOSAL = 'proposal';

    public const STATUS_NEGOTIATION = 'negotiation';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    /**
     * Lead source constants
     */
    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_REFERRAL = 'referral';

    public const SOURCE_PHONE = 'phone';

    public const SOURCE_EMAIL = 'email';

    public const SOURCE_SOCIAL_MEDIA = 'social_media';

    public const SOURCE_AFFILIATE = 'affiliate';

    public const SOURCE_OTHER = 'other';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_QUALIFIED,
            self::STATUS_PROPOSAL,
            self::STATUS_NEGOTIATION,
            self::STATUS_WON,
            self::STATUS_LOST,
        ];
    }

    /**
     * Get all available sources
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_WEBSITE,
            self::SOURCE_REFERRAL,
            self::SOURCE_PHONE,
            self::SOURCE_EMAIL,
            self::SOURCE_SOCIAL_MEDIA,
            self::SOURCE_AFFILIATE,
            self::SOURCE_OTHER,
        ];
    }

    /**
     * Check if lead is won
     */
    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    /**
     * Check if lead is lost
     */
    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Check if lead is converted
     */
    public function isConverted(): bool
    {
        return $this->converted_to_customer_id !== null;
    }

    /**
     * Get the user who is assigned to this lead
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this lead
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the customer this lead was converted to
     */
    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_to_customer_id');
    }

    /**
     * Get the activities for this lead
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by assigned user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to filter active leads (not won or lost)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_WON, self::STATUS_LOST]);
    }

    /**
     * Scope to filter converted leads
     */
    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_to_customer_id');
    }
}
