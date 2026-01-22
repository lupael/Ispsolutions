<?php

namespace App\Policies;

use App\Models\User;

/**
 * NetworkDevicePolicy
 *
 * Controls access to network devices (NAS, OLT, Router, Pools, etc.)
 *
 * Permission Rules:
 * - Only Admin (level 20) can add/manage NAS, OLT, and Router
 * - Only Admin can add/manage PPP profiles
 * - Only Admin can add/manage Pools
 * - Only Admin can add/manage Packages
 * - Only Admin can add/manage Package Prices
 * - If Admin provides explicit permission to Staff/Manager, they can view/edit/manage those resources
 */
class NetworkDevicePolicy
{
    /**
     * Determine if the user can view any network devices.
     *
     * Admin can always view.
     * Staff/Manager can view if they have explicit permission.
     */
    public function viewAny(User $user): bool
    {
        // Admin can always view
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can view if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.view') || $user->hasSpecialPermission('network.view');
        }

        // Developer and Super Admin can view
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view a specific network device.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if the user can create network devices (NAS, OLT, Router).
     *
     * Only Admin can create.
     * Staff/Manager can create only if they have explicit permission.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can create if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.manage') || $user->hasSpecialPermission('network.manage');
        }

        // Developer and Super Admin can create
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update network devices.
     *
     * Only Admin can update.
     * Staff/Manager can update only if they have explicit permission.
     */
    public function update(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine if the user can delete network devices.
     *
     * Only Admin can delete.
     * Staff/Manager can delete only if they have explicit permission.
     */
    public function delete(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine if the user can manage PPP profiles.
     *
     * Only Admin can manage PPP profiles.
     * Staff/Manager can manage if they have explicit permission.
     */
    public function managePppProfiles(User $user): bool
    {
        // Only Admin can manage
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can manage if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.manage') || $user->hasSpecialPermission('ppp.manage');
        }

        // Developer and Super Admin can manage
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can manage IP Pools.
     *
     * Only Admin can manage pools.
     * Staff/Manager can manage if they have explicit permission.
     */
    public function managePools(User $user): bool
    {
        // Only Admin can manage
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can manage if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.manage') || $user->hasSpecialPermission('pools.manage');
        }

        // Developer and Super Admin can manage
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }
}
