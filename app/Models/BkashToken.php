<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

/**
 * Bkash Token Model
 * 
 * Represents a payment token from Bkash for one-click payments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Bkash Tokenization
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.4
 * 
 * @property int $id
 * @property int $user_id
 * @property int $bkash_agreement_id
 * @property string $token Encrypted payment token
 * @property string $token_type Token type (bearer, etc.)
 * @property \Carbon\Carbon|null $expires_at Token expiration time
 * @property string $customer_msisdn Customer mobile number
 * @property bool $is_default Whether this is the default payment method
 * @property \Carbon\Carbon|null $last_used_at Last time token was used
 * @property int $usage_count Number of times token has been used
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read BkashAgreement $agreement
 */
class BkashToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bkash_agreement_id',
        'token',
        'token_type',
        'expires_at',
        'customer_msisdn',
        'is_default',
        'last_used_at',
        'usage_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'bkash_agreement_id' => 'integer',
        'is_default' => 'boolean',
        'usage_count' => 'integer',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who owns this token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agreement associated with this token
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(BkashAgreement::class, 'bkash_agreement_id');
    }

    /**
     * Get the decrypted token
     */
    public function getDecryptedToken(): string
    {
        return Crypt::decryptString($this->token);
    }

    /**
     * Set the encrypted token.
     *
     * Expects a plaintext token value and stores it encrypted.
     */
    public function setTokenAttribute(string $value): void
    {
        $this->attributes['token'] = Crypt::encryptString($value);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return ! $this->isExpired() && $this->agreement && $this->agreement->isActive();
    }

    /**
     * Mark token as used
     */
    public function markUsed(): void
    {
        $this->update([
            'usage_count'  => $this->usage_count + 1,
            'last_used_at' => now(),
        ]);
    }

    /**
     * Set as default payment method
     */
    public function setAsDefault(): void
    {
        // Remove default from all other tokens for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this token as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get masked mobile number for display
     */
    public function getMaskedMsisdn(): string
    {
        $msisdn = $this->customer_msisdn;
        $length = strlen($msisdn);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        // Show last 4 digits
        $masked = str_repeat('*', $length - 4) . substr($msisdn, -4);
        
        return $masked;
    }

    /**
     * Scope to get valid (non-expired) tokens
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get expired tokens
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope to get default token for a user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultFor($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_default', true);
    }

    /**
     * Scope to get tokens for a specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // When a token has been created and is marked as default, unset others for the same user
        static::created(function ($token) {
            if ($token->is_default) {
                self::where('user_id', $token->user_id)
                    ->where('id', '!=', $token->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
