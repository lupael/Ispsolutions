<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'phone_number',
        'username',
        'password',
        'otp_code',
        'otp_expires_at',
        'is_verified',
        'verified_at',
        'package_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'otp_code',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function isOtpValid(): bool
    {
        return $this->otp_expires_at && $this->otp_expires_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->is_verified && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }
}
