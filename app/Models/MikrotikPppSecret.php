<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MikrotikPppSecret - PPP Secret imported from MikroTik router
 * 
 * Following IspBills pattern for mikrotik_ppp_secret table
 */
class MikrotikPppSecret extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_import_id',
        'operator_id',
        'nas_id',
        'router_id',
        'name',
        'password',
        'profile',
        'remote_address',
        'disabled',
        'comment',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'disabled' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer import request
     */
    public function customerImport(): BelongsTo
    {
        return $this->belongsTo(CustomerImport::class, 'customer_import_id');
    }

    /**
     * Get the operator
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the NAS device
     */
    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class);
    }

    /**
     * Get the router
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }

    /**
     * Check if secret is disabled
     */
    public function isDisabled(): bool
    {
        return $this->disabled === 'yes';
    }

    /**
     * Check if secret is enabled
     */
    public function isEnabled(): bool
    {
        return $this->disabled === 'no';
    }
}
