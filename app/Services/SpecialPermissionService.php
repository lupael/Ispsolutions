<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SpecialPermission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SpecialPermissionService
{
    /**
     * Grant a special permission to a user
     */
    public function grantPermission(
        User $user,
        string $permissionKey,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?Carbon $expiresAt = null,
        ?string $description = null,
        ?User $grantedBy = null
    ): SpecialPermission {
        return SpecialPermission::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'permission_key' => $permissionKey,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'expires_at' => $expiresAt,
            'granted_by' => $grantedBy?->id ?? auth()->id(),
        ]);
    }

    /**
     * Revoke a special permission
     */
    public function revokePermission(SpecialPermission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * Check if a user has a specific special permission
     */
    public function hasPermission(
        User $user,
        string $permissionKey,
        ?string $resourceType = null,
        ?int $resourceId = null
    ): bool {
        $query = SpecialPermission::where('user_id', $user->id)
            ->where('permission_key', $permissionKey)
            ->active();

        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }

        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }

        return $query->exists();
    }

    /**
     * Get all active special permissions for a user
     */
    public function getUserPermissions(User $user): Collection
    {
        return SpecialPermission::where('user_id', $user->id)
            ->active()
            ->with(['grantedBy'])
            ->orderBy('granted_at', 'desc')
            ->get();
    }

    /**
     * Get all users with a specific permission
     */
    public function getUsersWithPermission(string $permissionKey, ?int $tenantId = null): Collection
    {
        $query = SpecialPermission::where('permission_key', $permissionKey)
            ->active()
            ->with(['user']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->pluck('user');
    }

    /**
     * Extend the expiration of a permission
     */
    public function extendPermission(SpecialPermission $permission, Carbon $newExpiresAt): bool
    {
        return $permission->update(['expires_at' => $newExpiresAt]);
    }

    /**
     * Get available permission keys
     */
    public function getAvailablePermissions(): array
    {
        return [
            // Customer permissions
            'customers.view_all' => 'View all customers across tenant',
            'customers.edit_all' => 'Edit all customers across tenant',
            'customers.delete_any' => 'Delete any customer',
            'customers.suspend_any' => 'Suspend any customer',
            
            // Invoice permissions
            'invoices.view_all' => 'View all invoices',
            'invoices.edit_all' => 'Edit all invoices',
            'invoices.void_any' => 'Void any invoice',
            
            // Payment permissions
            'payments.view_all' => 'View all payments',
            'payments.edit_all' => 'Edit all payments',
            'payments.refund_any' => 'Refund any payment',
            
            // Package permissions
            'packages.create' => 'Create packages',
            'packages.edit_all' => 'Edit all packages',
            'packages.delete_any' => 'Delete any package',
            
            // Router permissions
            'routers.manage_all' => 'Manage all routers',
            'routers.configure' => 'Configure routers',
            
            // Report permissions
            'reports.financial' => 'Access financial reports',
            'reports.customer' => 'Access customer reports',
            'reports.network' => 'Access network reports',
            
            // System permissions
            'system.settings' => 'Access system settings',
            'system.billing_config' => 'Configure billing settings',
        ];
    }

    /**
     * Clean up expired permissions
     */
    public function cleanupExpiredPermissions(?int $tenantId = null): int
    {
        $query = SpecialPermission::expired();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->delete();
    }
}
