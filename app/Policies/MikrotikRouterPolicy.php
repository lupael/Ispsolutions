<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MikrotikRouter;
use App\Models\User;

/**
 * MikrotikRouterPolicy
 *
 * Controls access to MikroTik Router devices and operations.
 *
 * Permission Rules:
 * - Only Admin (level 20) can manage routers
 * - Staff/Manager can view/manage if they have explicit permission
 * - Tenant isolation is enforced
 * - Additional permissions for configuration, backup, restore, and provisioning
 */
class MikrotikRouterPolicy
{
    /**
     * Determine if the user can view any routers.
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
     * Determine if the user can view a specific router.
     */
    public function view(User $user, MikrotikRouter $router): bool
    {
        // Must have viewAny permission
        if (! $this->viewAny($user)) {
            return false;
        }

        // Tenant isolation: User can only view routers in their tenant
        return $router->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can create routers.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        if ($user->isAdmin()) {
            return true;
        }

        // Staff/Manager can create if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return $user->hasPermission('network.manage') || $user->hasSpecialPermission('router.manage');
        }

        // Developer and Super Admin can create
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update a router.
     */
    public function update(User $user, MikrotikRouter $router): bool
    {
        // Must have create permission
        if (! $this->create($user)) {
            return false;
        }

        // Tenant isolation: User can only update routers in their tenant
        return $router->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can delete a router.
     */
    public function delete(User $user, MikrotikRouter $router): bool
    {
        // Must have create permission
        if (! $this->create($user)) {
            return false;
        }

        // Tenant isolation: User can only delete routers in their tenant
        return $router->tenant_id === getCurrentTenantId();
    }

    /**
     * Determine if the user can configure a router (RADIUS, PPP, Firewall).
     */
    public function configure(User $user, MikrotikRouter $router): bool
    {
        // Only Admin can configure
        if ($user->isAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        // Staff/Manager can configure if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return ($user->hasPermission('router.configure') || $user->hasSpecialPermission('router.configure'))
                && $router->tenant_id === getCurrentTenantId();
        }

        // Developer and Super Admin can configure
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        return false;
    }

    /**
     * Determine if the user can create backups for a router.
     */
    public function backup(User $user, MikrotikRouter $router): bool
    {
        // Only Admin can create backups
        if ($user->isAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        // Staff/Manager can create backups if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return ($user->hasPermission('router.backup') || $user->hasSpecialPermission('router.backup'))
                && $router->tenant_id === getCurrentTenantId();
        }

        // Developer and Super Admin can create backups
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        return false;
    }

    /**
     * Determine if the user can restore router configuration from backup.
     */
    public function restore(User $user, MikrotikRouter $router): bool
    {
        // Only Admin can restore
        if ($user->isAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        // Staff/Manager can restore if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return ($user->hasPermission('router.restore') || $user->hasSpecialPermission('router.restore'))
                && $router->tenant_id === getCurrentTenantId();
        }

        // Developer and Super Admin can restore
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        return false;
    }

    /**
     * Determine if the user can provision users to a router.
     */
    public function provision(User $user, MikrotikRouter $router): bool
    {
        // Only Admin can provision
        if ($user->isAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        // Staff/Manager can provision if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return ($user->hasPermission('router.provision') || $user->hasSpecialPermission('router.provision'))
                && $router->tenant_id === getCurrentTenantId();
        }

        // Developer and Super Admin can provision
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        return false;
    }

    /**
     * Determine if the user can manage failover settings for a router.
     */
    public function manageFailover(User $user, MikrotikRouter $router): bool
    {
        // Same permissions as configure
        return $this->configure($user, $router);
    }

    /**
     * Determine if the user can import data from a router.
     */
    public function import(User $user, MikrotikRouter $router): bool
    {
        // Only Admin can import
        if ($user->isAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        // Staff/Manager can import if they have explicit permission
        if ($user->isManager() || $user->isStaff()) {
            return ($user->hasPermission('router.import') || $user->hasSpecialPermission('router.import'))
                && $router->tenant_id === getCurrentTenantId();
        }

        // Developer and Super Admin can import
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return $router->tenant_id === getCurrentTenantId();
        }

        return false;
    }
}
