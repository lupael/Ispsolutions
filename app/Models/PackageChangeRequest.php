<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageChangeRequest extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'current_package_id',
        'requested_package_id',
        'request_type',
        'status',
        'reason',
        'admin_notes',
        'approved_by',
        'approved_at',
        'effective_date',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'effective_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'current_package_id');
    }

    public function requestedPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'requested_package_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
