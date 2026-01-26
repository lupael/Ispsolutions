<?php

namespace App\Policies;

use App\Models\SpecialPermission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpecialPermissionPolicy
{
    // Role level constants
    private const SUPER_ADMIN_LEVEL = 10;
    private const ADMIN_LEVEL = 20;

    /**
     * Check if user is Super Admin or Admin
     */
    private function isSuperAdminOrAdmin(User $user): bool
    {
        return in_array($user->role_level, [self::SUPER_ADMIN_LEVEL, self::ADMIN_LEVEL]);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdminOrAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SpecialPermission $specialPermission): bool
    {
        return $this->isSuperAdminOrAdmin($user) && 
               $user->tenant_id === $specialPermission->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdminOrAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SpecialPermission $specialPermission): bool
    {
        return $this->isSuperAdminOrAdmin($user) &&
               $user->tenant_id === $specialPermission->tenant_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SpecialPermission $specialPermission): bool
    {
        return $this->isSuperAdminOrAdmin($user) &&
               $user->tenant_id === $specialPermission->tenant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SpecialPermission $specialPermission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SpecialPermission $specialPermission): bool
    {
        return false;
    }
}
