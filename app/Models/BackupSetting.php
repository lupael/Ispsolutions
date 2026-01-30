<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'nas_id',
        'primary_authenticator',
    ];

    /**
     * Get the operator that owns this backup setting.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the NAS (router) associated with this backup setting.
     */
    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class, 'nas_id');
    }
}
