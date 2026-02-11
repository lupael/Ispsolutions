<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant Model
 *
 * Represents a multi-tenant application instance. Each tenant has its own
 * data isolation and configuration.
 *
 * @property int $id
 * @property string $name
 * @property string|null $domain
 * @property string|null $subdomain
 * @property string $database
 * @property array|null $settings
 * @property string $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PaymentGateway[] $paymentGateways
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IpPool[] $ipPools
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ServicePackage[] $servicePackages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NetworkUser[] $networkUsers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Olt[] $olts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Onu[] $onus
 * @property-read \App\Models\Subscription|null $subscription
 * @property-read \App\Models\User|null $admin
 */
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the user who created this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payment gateways for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentGateways(): HasMany
    {
        return $this->hasMany(PaymentGateway::class);
    }

    /**
     * Get the IP pools for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ipPools(): HasMany
    {
        return $this->hasMany(IpPool::class);
    }

    /**
     * Get the service packages for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicePackages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    /**
     * Get the network users for the tenant.
     * Note: NetworkUser is a shim model that maps to the 'users' table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class);
    }

    /**
     * Get the OLTs for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function olts(): HasMany
    {
        return $this->hasMany(Olt::class);
    }

    /**
     * Get the ONUs for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    /**
     * Get the subscription for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Get the admin user for the tenant.
     * This assumes 'operator_level' 20 corresponds to an admin role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->where('operator_level', 20);
    }

    /**
     * Check if tenant is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope a query to only include active tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get a specific setting for the tenant.
     *
     * @param string $key The key of the setting to retrieve.
     * @param mixed $default The default value if the setting is not found.
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
