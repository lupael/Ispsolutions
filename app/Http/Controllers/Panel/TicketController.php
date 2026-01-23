<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:technical,billing,general,complaint,feature_request',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => auth()->user()->tenant_id,
            'customer_id' => auth()->user()->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'],
            'category' => $validated['category'],
            'status' => Ticket::STATUS_OPEN,
            'created_by' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Ticket created successfully. Ticket #' . $ticket->id);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load(['customer', 'assignedTo', 'resolver', 'creator']);
        
        // Ensure user has access to this ticket
        $user = auth()->user();
        if (!$user->isDeveloper() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            // For customers, they can only view their own tickets
            if ($user->isCustomer() && $ticket->customer_id !== $user->id) {
                abort(403, 'Unauthorized access to ticket');
            }
            
            // For operators/staff, check if ticket belongs to their customers
            if ($user->isOperator() || $user->isSubOperator()) {
                $customerIds = $user->subordinates()
                    ->where('operator_level', User::OPERATOR_LEVEL_CUSTOMER)
                    ->pluck('id');
                if (!$customerIds->contains($ticket->customer_id)) {
                    abort(403, 'Unauthorized access to ticket');
                }
            }
        }
        
        return view('panels.shared.tickets.show', compact('ticket'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,pending,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'resolution_notes' => 'sometimes|nullable|string',
        ]);

        // If status is being changed to resolved, set resolved_at and resolved_by
        if (isset($validated['status']) && $validated['status'] === Ticket::STATUS_RESOLVED) {
            $validated['resolved_at'] = now();
            $validated['resolved_by'] = auth()->user()->id;
        }

        $ticket->update($validated);

        return redirect()->back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()->back()->with('success', 'Ticket deleted successfully.');
    }
}
