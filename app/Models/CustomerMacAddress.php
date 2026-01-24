<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerMacAddress extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'mac_address',
        'device_name',
        'status',
        'first_seen_at',
        'last_seen_at',
        'notes',
        'added_by',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the user that owns the MAC address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who added this MAC address.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Format MAC address to standard format (XX:XX:XX:XX:XX:XX)
     */
    public static function formatMacAddress(string $mac): string
    {
        // Remove all non-alphanumeric characters
        $mac = preg_replace('/[^A-Fa-f0-9]/', '', $mac);
        
        // Ensure it's 12 characters
        if (strlen($mac) !== 12) {
            return '';
        }
        
        // Format as XX:XX:XX:XX:XX:XX
        return strtoupper(implode(':', str_split($mac, 2)));
    }

    /**
     * Check if MAC address is valid
     */
    public static function isValidMacAddress(string $mac): bool
    {
        $formatted = self::formatMacAddress($mac);
        return !empty($formatted);
    }
}
