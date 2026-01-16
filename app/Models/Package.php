<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'bandwidth_upload',
        'bandwidth_download',
        'validity_days',
        'billing_type',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'bandwidth_upload' => 'integer',
        'bandwidth_download' => 'integer',
        'validity_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class, 'package_id');
    }
}
