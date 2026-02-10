<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'credential_id',
        'public_key',
        'credential_public_key',
        'counter',
        'transports',
        'aaguid',
        'attestation_type',
        'is_primary',
        'is_active',
        'registration_ip',
        'user_agent',
        'last_used_at',
        'last_used_ip',
    ];

    protected $casts = [
        'counter' => 'integer',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'transports' => 'json',
    ];

    /**
     * Get the user that owns this credential
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark this credential as the primary one (disabling others)
     */
    public function makePrimary(): void
    {
        // Unset other primary credentials for this user
        $this->user->webAuthnCredentials()->update(['is_primary' => false]);
        
        // Set this one as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Record a successful use of this credential
     */
    public function recordUsage($ipAddress = null, $userAgent = null): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ipAddress,
        ]);
    }

    /**
     * Check if this credential is still valid
     */
    public function isValid(): bool
    {
        return $this->is_active && $this->user && $this->user->is_active;
    }

    /**
     * Get active credentials only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Increment the signature counter to detect cloning attempts
     */
    public function incrementCounter($newCounter): void
    {
        if ($newCounter <= $this->counter) {
            throw new \Exception('Possible cloning detected. Counter mismatch.');
        }
        $this->update(['counter' => $newCounter]);
    }
}
