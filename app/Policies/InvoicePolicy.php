<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine if the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager', 'staff']);
    }

    /**
     * Determine if the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Super admin and admin can view all invoices
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Users can view their own invoices
        if ($user->id === $invoice->user_id) {
            return true;
        }

        // Manager and staff can view invoices within their tenant
        if ($user->hasAnyRole(['manager', 'staff']) && $user->tenant_id === $invoice->tenant_id) {
            return true;
        }

        // Operators and sub-operators can view invoices for their customers
        if ($user->isOperatorRole() || $user->isSubOperator()) {
            return $user->subordinates()
                ->where('operator_level', User::OPERATOR_LEVEL_CUSTOMER)
                ->where('id', $invoice->user_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    /**
     * Determine if the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Only admins and managers can update invoices
        if ($user->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            return $user->tenant_id === $invoice->tenant_id || $user->hasRole('super-admin');
        }

        return false;
    }

    /**
     * Determine if the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only super-admin and admin can delete invoices
        return $user->hasAnyRole(['super-admin', 'admin']) &&
               ($user->tenant_id === $invoice->tenant_id || $user->hasRole('super-admin'));
    }

    /**
     * Determine if the user can pay the invoice.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        // Users can pay their own invoices
        if ($user->id === $invoice->user_id && $invoice->status !== 'paid') {
            return true;
        }

        // Admins can pay any invoice in their tenant
        if ($user->hasAnyRole(['super-admin', 'admin', 'manager']) &&
            ($user->tenant_id === $invoice->tenant_id || $user->hasRole('super-admin'))) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can record manual payment for the invoice.
     */
    public function recordPayment(User $user, Invoice $invoice): bool
    {
        // Only admins and managers can record manual payments
        return $user->hasAnyRole(['super-admin', 'admin', 'manager']) &&
               ($user->tenant_id === $invoice->tenant_id || $user->hasRole('super-admin'));
    }
}
