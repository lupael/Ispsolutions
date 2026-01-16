<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (! is_array($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Get all permissions for the role.
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }
}
