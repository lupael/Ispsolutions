<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperatorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any operators.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_operators') || $user->operator_level <= 20;
    }

    /**
     * Determine if the user can view the operator.
     */
    public function view(User $user, User $operator): bool
    {
        // Can view if has permission and operator level is higher (lower privilege)
        return ($user->hasPermission('view_operators') || $user->operator_level <= 20)
            && $user->operator_level < $operator->operator_level;
    }

    /**
     * Determine if the user can create operators.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_operators') || $user->operator_level <= 20;
    }

    /**
     * Determine if the user can update the operator.
     */
    public function update(User $user, User $operator): bool
    {
        // Can update if has permission and operator level is higher (lower privilege)
        // Cannot update self's level
        if ($user->id === $operator->id) {
            return false;
        }

        return ($user->hasPermission('edit_operators') || $user->operator_level <= 20)
            && $user->operator_level < $operator->operator_level;
    }

    /**
     * Determine if the user can delete the operator.
     */
    public function delete(User $user, User $operator): bool
    {
        // Can delete if has permission and operator level is higher (lower privilege)
        if ($user->id === $operator->id) {
            return false;
        }

        return ($user->hasPermission('delete_operators') || $user->operator_level <= 20)
            && $user->operator_level < $operator->operator_level;
    }

    /**
     * Determine if the user can manage permissions for the operator.
     */
    public function managePermissions(User $user, User $operator): bool
    {
        // Only Group Admin and above can manage permissions
        if ($user->operator_level > 20) {
            return false;
        }

        // Cannot manage permissions for self or higher/equal level operators
        if ($user->id === $operator->id || $user->operator_level >= $operator->operator_level) {
            return false;
        }

        return $user->hasPermission('manage_permissions');
    }
}
