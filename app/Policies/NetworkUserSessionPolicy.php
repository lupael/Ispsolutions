<?php

namespace App\Policies;

use App\Models\NetworkUserSession;
use App\Models\User;

/**
 * NetworkUserSessionPolicy
 *
 * Controls access to network user sessions.
 *
 * Permission Rules:
 * - Admin can view and disconnect any session in their tenant
 * - Manager can view and disconnect sessions in their tenant
 * - Staff can view sessions but may need permission to disconnect
 */
class NetworkUserSessionPolicy
{
    /**
     * Determine if the user can view the session.
     */
    public function view(User $user, NetworkUserSession $session): bool
    {
        // Load the related user if not already loaded
        if (!$session->relationLoaded('user')) {
            $session->load('user');
        }

        // Must be in the same tenant (via session's user)
        if ($session->user && $user->tenant_id !== $session->user->tenant_id) {
            return false;
        }

        // Admin and Manager can view
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Staff can view if they have permission
        if ($user->isStaff()) {
            return $user->hasPermission('sessions.view');
        }

        // Developer and Super Admin can view
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can disconnect the session.
     */
    public function disconnect(User $user, NetworkUserSession $session): bool
    {
        // Load the related user if not already loaded
        if (!$session->relationLoaded('user')) {
            $session->load('user');
        }

        // Must be in the same tenant (via session's user)
        if ($session->user && $user->tenant_id !== $session->user->tenant_id) {
            return false;
        }

        // Admin and Manager can disconnect
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Staff can disconnect if they have permission
        if ($user->isStaff()) {
            return $user->hasPermission('sessions.disconnect');
        }

        // Developer and Super Admin can disconnect
        if ($user->isDeveloper() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }
}
