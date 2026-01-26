<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Nas;
use App\Models\User;

/**
 * NasPolicy
 *
 * Controls access to Network Access Server (NAS) devices.
 *
 * Permission Rules:
 * - Only Admin (level 20) can manage NAS devices
 * - Staff/Manager can view/manage if they have explicit permission
 * - Tenant isolation is enforced
 */
class NasPolicy
{
    /**
     * Determine if the user can view any NAS devices.
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
     * Determine if the user can view a specific NAS device.
     */
    public function view(User $user, Nas $nas): bool
    {
        // Must have viewAny permission
        if (! $this->viewAny($user)) {
            return false;
        }

        // Tenant isolation: User can only view NAS devices in their tenant
        return $nas->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can create NAS devices.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can create if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.manage') || $user->hasSpecialPermission('nas.manage');
        }

        // Developer and Super Admin can create
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update a NAS device.
     */
    public function update(User $user, Nas $nas): bool
    {
        // Must have create permission
        if (! $this->create($user)) {
            return false;
        }

        // Tenant isolation: User can only update NAS devices in their tenant
        return $nas->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can delete a NAS device.
     */
    public function delete(User $user, Nas $nas): bool
    {
        // Must have create permission
        if (! $this->create($user)) {
            return false;
        }

        // Tenant isolation: User can only delete NAS devices in their tenant
        return $nas->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can test connectivity to a NAS device.
     */
    public function testConnection(User $user, Nas $nas): bool
    {
        // Same permissions as view
        return $this->view($user, $nas);
    }
}
