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
        return $user->isStaffMember() || $user->hasPermission('view_customers');
    }

    /**
     * Determine if the user can view the customer.
     */
    public function view(User $user, User $customer): bool
    {
        // A user can view a customer if they have the general permission and can manage that specific user based on hierarchy.
        return ($user->hasPermission('view_customers') || $user->operator_level <= 80) && $user->canManage($customer);
    }

    /**
     * Determine if the user can create customers.
     */
    public function create(User $user): bool
    {
        // Use the dedicated method on the User model for clarity.
        return $user->canCreateCustomer();
    }

    /**
     * Determine if the user can update the customer.
     */
    public function update(User $user, User $customer): bool
    {
        // A user can update a customer if they have the general permission and can manage that specific user.
        return ($user->hasPermission('edit_customers') || $user->operator_level <= 40) && $user->canManage($customer);
    }

    /**
     * Determine if the user can delete the customer.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function delete(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can restore a soft-deleted customer.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function restore(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can permanently delete a customer.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function forceDelete(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can suspend the customer.
     */
    public function suspend(User $user, User $customer): bool
    {
        return ($user->hasPermission('suspend_customers') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can activate the customer.
     */
    public function activate(User $user, User $customer): bool
    {
        return ($user->hasPermission('activate_customers') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can disconnect the customer.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function disconnect(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can change customer's package.
     */
    public function changePackage(User $user, User $customer): bool
    {
        return ($user->hasPermission('change_package') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can edit speed limits.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function editSpeedLimit(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can activate FUP.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function activateFup(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can remove MAC binding.
     */
    public function removeMacBind(User $user, User $customer): bool
    {
        return ($user->hasPermission('remove_mac_bind') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can generate bills.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function generateBill(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can edit billing profile.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function editBillingProfile(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can send SMS.
     */
    public function sendSms(User $user, User $customer): bool
    {
        return ($user->hasPermission('send_sms') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can send payment link.
     */
    public function sendLink(User $user, User $customer): bool
    {
        return ($user->hasPermission('send_payment_link') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can record advance payment.
     */
    public function advancePayment(User $user, User $customer): bool
    {
        return ($user->hasPermission('record_payments') || $this->isAdminOrHigher($user)) && $user->canManage($customer);
    }

    /**
     * Determine if the user can change operator.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function changeOperator(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user);
    }

    /**
     * Determine if the user can edit suspend date.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function editSuspendDate(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can perform daily recharge (for daily billing).
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function dailyRecharge(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Determine if the user can perform hotspot recharge.
     * ADMIN ONLY - Not available to Operator/Sub-Operator.
     */
    public function hotspotRecharge(User $user, User $customer): bool
    {
        return $this->isAdminOrHigher($user) && $user->canManage($customer);
    }

    /**
     * Helper to check for Admin, Super Admin, or Developer roles.
     */
    private function isAdminOrHigher(User $user): bool
    {
        // operator_level <= 20 covers Admin, Super Admin, and Developer.
        return $user->operator_level <= User::OPERATOR_LEVEL_ADMIN;
    }

    /**
     * Task 7.5: Add reseller permissions
     * Determine if a reseller can manage a child account
     */
    public function manageChildAccount(User $reseller, User $customer): bool
    {
        return $reseller->canManage($customer);
    }

    /**
     * Task 7.5: Check if user can view reseller dashboard
     */
    public function viewResellerDashboard(User $user): bool
    {
        // User must have child accounts to be considered a reseller
        return $user->childAccounts()->exists() || $user->operator_level <= 20;
    }

    /**
     * Task 7.5: Determine if user can assign child accounts
     */
    public function assignChildAccount(User $user): bool
    {
        // Only admins and verified resellers can assign child accounts
        return $user->operator_level <= 20 ||
               ($user->is_reseller ?? false) === true;
    }
}
