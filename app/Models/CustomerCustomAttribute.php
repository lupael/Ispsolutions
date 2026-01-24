<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCustomAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'custom_field_id',
        'value',
    ];

    /**
     * Get the customer that owns the attribute.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the custom field definition.
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomerCustomField::class, 'custom_field_id');
    }
}
