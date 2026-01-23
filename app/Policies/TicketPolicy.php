<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tickets (with role-based filtering in controller)
        return true;
    }

    /**
     * Determine if the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Developers, Super Admins, and Admins can view all tickets in their scope
        if ($user->isDeveloper() || $user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Customers can only view their own tickets
        if ($user->isCustomer() && $ticket->customer_id === $user->id) {
            return true;
        }

        // Staff can view tickets assigned to them or unassigned tickets
        if ($user->isStaff()) {
            return $ticket->assigned_to === $user->id || $ticket->assigned_to === null;
        }

        // Operators and Sub-Operators can view tickets from their customers
        if ($user->isOperatorRole() || $user->isSubOperator()) {
            $customerIds = $user->subordinates()
                ->where('operator_level', User::OPERATOR_LEVEL_CUSTOMER)
                ->pluck('id');
            return $customerIds->contains($ticket->customer_id);
        }

        return false;
    }

    /**
     * Determine if the user can create tickets.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create tickets
        return true;
    }

    /**
     * Determine if the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Developers, Super Admins, and Admins can update any ticket
        if ($user->isDeveloper() || $user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Customers cannot update tickets
        if ($user->isCustomer()) {
            return false;
        }

        // Staff can only update tickets assigned to them
        if ($user->isStaff()) {
            return $ticket->assigned_to === $user->id;
        }

        // Operators and Sub-Operators can update tickets from their customers
        if ($user->isOperatorRole() || $user->isSubOperator()) {
            $customerIds = $user->subordinates()
                ->where('operator_level', User::OPERATOR_LEVEL_CUSTOMER)
                ->pluck('id');
            return $customerIds->contains($ticket->customer_id);
        }

        return false;
    }

    /**
     * Determine if the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only Admins, Super Admins, and Developers can delete tickets
        return $user->isDeveloper() || $user->isSuperAdmin() || $user->isAdmin();
    }
}
