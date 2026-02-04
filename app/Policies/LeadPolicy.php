<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Determine if the user can view any leads.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'isp', 'sales-manager', 'manager']);
    }

    /**
     * Determine if the user can view the lead.
     */
    public function view(User $user, Lead $lead): bool
    {
        // Super admin can view all leads
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin and sales manager can view leads within their tenant
        if ($user->hasAnyRole(['isp', 'sales-manager', 'manager']) && $user->tenant_id === $lead->tenant_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create leads.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'isp', 'sales-manager', 'manager']);
    }

    /**
     * Determine if the user can update the lead.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Super admin can update any lead
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin and sales manager can update leads within their tenant
        if ($user->hasAnyRole(['isp', 'sales-manager', 'manager'])) {
            return $user->tenant_id === $lead->tenant_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the lead.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Only super-admin and admin can delete leads
        return $user->hasAnyRole(['super-admin', 'isp']) &&
               ($user->tenant_id === $lead->tenant_id || $user->hasRole('super-admin'));
    }

    /**
     * Determine if the user can convert the lead to a customer.
     */
    public function convert(User $user, Lead $lead): bool
    {
        // Super admin can convert any lead
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin and sales manager can convert leads within their tenant
        if ($user->hasAnyRole(['isp', 'sales-manager', 'manager'])) {
            return $user->tenant_id === $lead->tenant_id && !$lead->isConverted();
        }

        return false;
    }
}
