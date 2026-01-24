<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCustomField extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'label',
        'type',
        'required',
        'options',
        'order',
        'visibility',
        'category',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'visibility' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the custom attributes for this field.
     */
    public function customAttributes(): HasMany
    {
        return $this->hasMany(CustomerCustomAttribute::class, 'custom_field_id');
    }

    /**
     * Check if the field is visible for a specific role.
     */
    public function isVisibleForRole(string $role): bool
    {
        if (empty($this->visibility)) {
            return true; // Visible to all if not specified
        }

        return in_array($role, $this->visibility);
    }

    /**
     * Scope to order by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }

        return $query;
    }
}
