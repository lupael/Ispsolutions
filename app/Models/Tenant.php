<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'domain',
        'subdomain',
        'database',
        'settings',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the user who created this tenant (Super Admin).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payment gateways for the tenant.
     */
    public function paymentGateways(): HasMany
    {
        return $this->hasMany(PaymentGateway::class);
    }

    /**
     * Get the IP pools for the tenant.
     */
    public function ipPools(): HasMany
    {
        return $this->hasMany(IpPool::class);
    }

    /**
     * Get the service packages for the tenant.
     */
    public function servicePackages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    /**
     * Get the network users for the tenant.
     */
    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class);
    }

    /**
     * Get the OLTs for the tenant.
     */
    public function olts(): HasMany
    {
        return $this->hasMany(Olt::class);
    }

    /**
     * Get the ONUs for the tenant.
     */
    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    /**
     * Get the subscription for the tenant.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope a query to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
