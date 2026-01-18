<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperatorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any operators.
     * View-only roles (Manager, Staff, Accountant) can view if they have permissions.
     */
    public function viewAny(User $user): bool
    {
        // View-only roles need explicit permission
        if ($user->hasViewOnlyAccess()) {
            return $user->hasPermission('view_operators');
        }

        // Other roles can view if they have permission or are Admin level or higher
        return $user->hasPermission('view_operators') || $user->operator_level <= 20;
    }

    /**
     * Determine if the user can view the operator.
     * Ensures proper tenant boundaries and role hierarchy.
     */
    public function view(User $user, User $operator): bool
    {
        // View-only roles need explicit permission
        if ($user->hasViewOnlyAccess()) {
            return $user->hasPermission('view_operators') && $user->tenant_id === $operator->tenant_id;
        }

        // Can view if has permission and can manage this operator
        return ($user->hasPermission('view_operators') || $user->operator_level <= 20)
            && $user->canManage($operator);
    }

    /**
     * Determine if the user can create operators.
     * Enforces the creation hierarchy:
     * - Developer: Can create Super Admins
     * - Super Admin: Can create Admins within their tenants
     * - Admin: Can create Operators within their ISP
     * - Operator: Can create Sub-Operators
     */
    public function create(User $user): bool
    {
        // View-only roles cannot create operators
        if ($user->hasViewOnlyAccess()) {
            return false;
        }

        // Must have permission and be at Admin level or higher
        return ($user->hasPermission('create_operators') || $user->operator_level <= 20);
    }

    /**
     * Determine if the user can create a user with a specific operator level.
     * Uses the canCreateUserWithLevel method to enforce hierarchy.
     */
    public function createWithLevel(User $user, int $targetLevel): bool
    {
        return $user->canCreateUserWithLevel($targetLevel);
    }

    /**
     * Determine if the user can update the operator.
     * Cannot update self's level. Must respect tenant boundaries.
     */
    public function update(User $user, User $operator): bool
    {
        // View-only roles cannot update operators
        if ($user->hasViewOnlyAccess()) {
            return false;
        }

        // Cannot update self's level
        if ($user->id === $operator->id) {
            return false;
        }

        // Must have permission and can manage this operator
        return ($user->hasPermission('edit_operators') || $user->operator_level <= 20)
            && $user->canManage($operator);
    }

    /**
     * Determine if the user can delete the operator.
     * Cannot delete self. Must respect tenant boundaries.
     */
    public function delete(User $user, User $operator): bool
    {
        // View-only roles cannot delete operators
        if ($user->hasViewOnlyAccess()) {
            return false;
        }

        // Cannot delete self
        if ($user->id === $operator->id) {
            return false;
        }

        // Must have permission and can manage this operator
        return ($user->hasPermission('delete_operators') || $user->operator_level <= 20)
            && $user->canManage($operator);
    }

    /**
     * Determine if the user can manage permissions for the operator.
     * Only Admin and above can manage permissions.
     * Cannot manage permissions for self or higher/equal level operators.
     */
    public function managePermissions(User $user, User $operator): bool
    {
        // View-only roles cannot manage permissions
        if ($user->hasViewOnlyAccess()) {
            return false;
        }

        // Only Admin and above can manage permissions
        if ($user->operator_level > 20) {
            return false;
        }

        // Cannot manage permissions for self or higher/equal level operators
        if ($user->id === $operator->id || $user->operator_level >= $operator->operator_level) {
            return false;
        }

        // Must be able to manage this operator and have permission
        return $user->canManage($operator) && $user->hasPermission('manage_permissions');
    }
}
