<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
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
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (! is_array($this->permissions)) {
            return false;
        }

        // Check for wildcard permission (super admin)
        if (in_array('*', $this->permissions)) {
            return true;
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

    /**
     * Get the display label for this role in a specific tenant.
     * Returns custom label if set, otherwise returns the default role name.
     *
     * @param int|null $tenantId Tenant ID (if null, uses current tenant from auth)
     */
    public function getDisplayLabel(?int $tenantId = null): string
    {
        if (! $tenantId && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }

        if (! $tenantId) {
            return $this->name;
        }

        return \App\Models\RoleLabelSetting::getDisplayLabel($tenantId, $this->slug, $this->name);
    }
}
