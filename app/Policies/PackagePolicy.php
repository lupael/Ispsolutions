<?php

namespace App\Policies;

use App\Models\User;

/**
 * PackagePolicy
 * 
 * Controls access to service packages and pricing.
 * 
 * Permission Rules:
 * - Only Admin (level 20) can add/manage Packages
 * - Only Admin can add/manage Package Prices
 * - If Admin provides explicit permission to Staff/Manager, they can view/edit/manage packages
 * - Operators can view packages but cannot manage or override pricing set by Admin
 * - Operators can set prices for their Sub-Operators only, but cannot manage or override pricing set by Admin
 */
class PackagePolicy
{
    /**
     * Determine if the user can view any packages.
     */
    public function viewAny(User $user): bool
    {
        // Admin can always view
        if ($user->isAdmin()) {
            return true;
        }

        // Operators and Sub-Operators can view packages
        if ($user->isOperatorRole() || $user->isSubOperator()) {
            return true;
        }

        // Staff/Manager can view if they have permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('packages.view') || $user->hasSpecialPermission('packages.view');
        }

        // Developer and Super Admin can view
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view a specific package.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if the user can create packages.
     * 
     * Only Admin can create packages.
     * Staff/Manager can create if they have explicit permission.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can create if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('packages.manage') || $user->hasSpecialPermission('packages.manage');
        }

        // Developer and Super Admin can create
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update packages.
     * 
     * Only Admin can update packages.
     * Staff/Manager can update if they have explicit permission.
     */
    public function update(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine if the user can delete packages.
     * 
     * Only Admin can delete packages.
     * Staff/Manager can delete if they have explicit permission.
     */
    public function delete(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine if the user can manage package prices.
     * 
     * Only Admin can manage base pricing.
     * Staff/Manager can manage if they have explicit permission.
     */
    public function manageBasePricing(User $user): bool
    {
        // Only Admin can manage base pricing
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can manage if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('packages.manage') || $user->hasSpecialPermission('pricing.manage');
        }

        // Developer and Super Admin can manage
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can set custom prices for their sub-operators.
     * 
     * Operators can set prices for their Sub-Operators only.
     * They cannot manage or override the base pricing set by Admin.
     */
    public function setSubOperatorPricing(User $user): bool
    {
        // Operators can set prices for their Sub-Operators
        if ($user->isOperatorRole()) {
            return true;
        }

        // Admin and above can also set Sub-Operator pricing
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isDeveloper()) {
            return true;
        }

        return false;
    }
}
