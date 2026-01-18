<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_customers') || $user->operator_level <= 80;
    }

    /**
     * Determine if the user can view the customer.
     */
    public function view(User $user, User $customer): bool
    {
        // Developer and Super Admin can view all
        if ($user->operator_level <= 10) {
            return true;
        }

        // Check if user has permission
        if (! $user->hasPermission('view_customers')) {
            return false;
        }

        // Check tenant isolation
        if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Check if has special permission to access all customers
        if ($this->hasSpecialPermission($user, 'access_all_customers')) {
            return true;
        }

        // Check manager hierarchy (if manager_id is set)
        if ($customer->manager_id === $user->id) {
            return true;
        }

        // Check if customer belongs to same zone/area
        // TODO: Implement zone/area check
        // For now, deny access by default to prevent bypassing zone restrictions
        return false;
    }

    /**
     * Determine if the user can create customers.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_customers') || $user->operator_level <= 30;
    }

    /**
     * Determine if the user can update the customer.
     */
    public function update(User $user, User $customer): bool
    {
        // Check basic permission
        if (! $user->hasPermission('edit_customers')) {
            return false;
        }

        // Check tenant isolation
        if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Developer and Super Admin can edit all
        if ($user->operator_level <= 10) {
            return true;
        }

        // Check if has special permission
        if ($this->hasSpecialPermission($user, 'access_all_customers')) {
            return true;
        }

        // Check manager hierarchy
        if ($customer->manager_id === $user->id) {
            return true;
        }

        // TODO: Implement zone/area-based access control
        // For now, deny access by default to prevent unauthorized access
        return false;
    }

    /**
     * Determine if the user can delete the customer.
     */
    public function delete(User $user, User $customer): bool
    {
        // Only high-level operators can delete
        if ($user->operator_level > 30) {
            return false;
        }

        // Check permission
        if (! $user->hasPermission('delete_customers')) {
            return false;
        }

        // Check tenant isolation
        if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can suspend the customer.
     */
    public function suspend(User $user, User $customer): bool
    {
        return $this->update($user, $customer) && $user->hasPermission('suspend_customers');
    }

    /**
     * Determine if the user can activate the customer.
     */
    public function activate(User $user, User $customer): bool
    {
        return $this->update($user, $customer) && $user->hasPermission('activate_customers');
    }

    /**
     * Check if user has a special permission.
     */
    private function hasSpecialPermission(User $user, string $permission): bool
    {
        return $user->operatorPermissions()
            ->where('permission_key', $permission)
            ->where('is_enabled', true)
            ->exists();
    }
}
