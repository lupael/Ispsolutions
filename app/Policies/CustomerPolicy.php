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
        // Developer, Super Admin, and Admin can view all
        if ($user->operator_level <= 20) {
            // Check tenant isolation
            if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
                return false;
            }
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

        // Check if customer is in user's management hierarchy
        if ($this->isInHierarchy($user, $customer)) {
            return true;
        }

        // Operators and sub-operators can view their own customers
        if ($user->operator_level <= 40) {
            return $this->isInHierarchy($user, $customer);
        }

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
        // Developer, Super Admin, and Admin can edit all customers (no permission check needed)
        if ($user->operator_level <= 20) {
            // Check tenant isolation
            if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
                return false;
            }
            return true;
        }

        // For other roles, check permission
        if (! $user->hasPermission('edit_customers')) {
            return false;
        }

        // Check tenant isolation
        if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Check if has special permission
        if ($this->hasSpecialPermission($user, 'access_all_customers')) {
            return true;
        }

        // Check manager hierarchy
        if ($customer->manager_id === $user->id) {
            return true;
        }

        // Check if customer is in user's management hierarchy
        return $this->isInHierarchy($user, $customer);
    }

    /**
     * Check if customer is in user's zone or area.
     *
     * Note: Zone/area-based restrictions are not currently enforced because the
     * corresponding attributes (zone_id, area_id) are not available on the User model.
     * Access control is handled by other checks in this policy (tenant, hierarchy,
     * permissions, etc.).
     */
    private function isSameZoneOrArea(User $user, User $customer): bool
    {
        // If zone/area fields are added in the future, implement checks here
        // For now, return false to not grant automatic access
        return false;
    }

    /**
     * Check if customer is in user's management hierarchy.
     */
    private function isInHierarchy(User $user, User $customer): bool
    {
        // Check if user created this customer
        if (isset($customer->created_by) && $customer->created_by === $user->id) {
            return true;
        }

        // Check if customer is in user's subordinates
        return $user->subordinates()->where('id', $customer->id)->exists();
    }

    /**
     * Determine if the user can delete the customer.
     */
    public function delete(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            // Check tenant isolation
            if ($user->tenant_id && $customer->tenant_id !== $user->tenant_id) {
                return false;
            }
            return true;
        }

        // Only Operators and above can delete (Sub-Operators and below cannot)
        if ($user->operator_level > 30) {
            return false;
        }

        // Check permission for Operator role
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
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('suspend_customers');
    }

    /**
     * Determine if the user can activate the customer.
     */
    public function activate(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('activate_customers');
    }

    /**
     * Determine if the user can disconnect the customer.
     */
    public function disconnect(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('disconnect_customers');
    }

    /**
     * Determine if the user can change customer's package.
     */
    public function changePackage(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('change_package');
    }

    /**
     * Determine if the user can edit speed limits.
     */
    public function editSpeedLimit(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('edit_speed_limit');
    }

    /**
     * Determine if the user can activate FUP.
     */
    public function activateFup(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('activate_fup');
    }

    /**
     * Determine if the user can remove MAC binding.
     */
    public function removeMacBind(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('remove_mac_bind');
    }

    /**
     * Determine if the user can generate bills.
     */
    public function generateBill(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $user->hasPermission('generate_bills') && $this->view($user, $customer);
    }

    /**
     * Determine if the user can edit billing profile.
     */
    public function editBillingProfile(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('edit_billing_profile');
    }

    /**
     * Determine if the user can send SMS.
     */
    public function sendSms(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $user->hasPermission('send_sms') && $this->view($user, $customer);
    }

    /**
     * Determine if the user can send payment link.
     */
    public function sendLink(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $user->hasPermission('send_payment_link') && $this->view($user, $customer);
    }

    /**
     * Determine if the user can record advance payment.
     */
    public function advancePayment(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $user->hasPermission('record_payments') && $this->view($user, $customer);
    }

    /**
     * Determine if the user can change operator.
     */
    public function changeOperator(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return true;
        }
        
        // Only Operators and above can transfer customers (Sub-Operators and below cannot)
        if ($user->operator_level > 30) {
            return false;
        }
        
        // Operators need permission
        return $user->hasPermission('change_operator');
    }

    /**
     * Determine if the user can edit suspend date.
     */
    public function editSuspendDate(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('edit_suspend_date');
    }

    /**
     * Determine if the user can perform daily recharge (for daily billing).
     */
    public function dailyRecharge(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('daily_recharge');
    }

    /**
     * Determine if the user can perform hotspot recharge.
     */
    public function hotspotRecharge(User $user, User $customer): bool
    {
        // Developer, Super Admin, and Admin have automatic access
        if ($user->operator_level <= 20) {
            return $this->view($user, $customer);
        }
        
        return $this->update($user, $customer) && $user->hasPermission('hotspot_recharge');
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
