<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'tenant_id',
        'operator_id',
        'master_package_id',
        'operator_package_rate_id',
        'name',
        'description',
        'bandwidth_up',
        'bandwidth_down',
        'price',
        'billing_cycle',
        'billing_type',
        'validity_days',
        'status',
        'is_active',
        'visibility',
        'pppoe_profile_id',
    ];

    protected $casts = [
        'bandwidth_up' => 'integer',
        'bandwidth_down' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'operator_id' => 'integer',
        'master_package_id' => 'integer',
        'operator_package_rate_id' => 'integer',
        'pppoe_profile_id' => 'integer',
        'visibility' => 'string',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function masterPackage(): BelongsTo
    {
        return $this->belongsTo(MasterPackage::class, 'master_package_id');
    }

    public function operatorPackageRate(): BelongsTo
    {
        return $this->belongsTo(OperatorPackageRate::class, 'operator_package_rate_id');
    }

    public function pppoeProfile(): BelongsTo
    {
        return $this->belongsTo(MikrotikProfile::class, 'pppoe_profile_id');
    }
}
