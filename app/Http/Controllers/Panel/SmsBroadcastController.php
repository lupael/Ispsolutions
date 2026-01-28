<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SmsBroadcastJob;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsBroadcastController extends Controller
{
    /**
     * Display all broadcast jobs.
     */
    public function index()
    {
        $broadcasts = SmsBroadcastJob::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panel.sms.broadcast.index', compact('broadcasts'));
    }

    /**
     * Show form to create broadcast.
     */
    public function create()
    {
        $zones = Zone::where('is_active', true)->get();

        return view('panel.sms.broadcast.create', compact('zones'));
    }

    /**
     * Store a new broadcast job.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:160', // SMS character limit
            'recipient_type' => 'required|in:all,customers,operators,active_customers,inactive_customers,specific_zone',
            'zone_id' => 'required_if:recipient_type,specific_zone|exists:zones,id',
            'scheduled_at' => 'nullable|date|after:now',
        ], [
            'message.max' => 'SMS message cannot exceed 160 characters (standard SMS limit).',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ]);

        $filters = [];
        if ($request->input('recipient_type') === 'specific_zone') {
            $filters['zone_id'] = $request->input('zone_id');
        }

        // Calculate total recipients based on filters
        $totalRecipients = $this->calculateRecipients($request->input('recipient_type'), $filters);

        SmsBroadcastJob::create([
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'recipient_type' => $request->input('recipient_type'),
            'filters' => $filters,
            'total_recipients' => $totalRecipients,
            'status' => 'pending',
            'scheduled_at' => $request->input('scheduled_at'),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('panel.sms.broadcast.index')
            ->with('success', 'Broadcast job created successfully and will be processed soon.');
    }

    /**
     * Display a specific broadcast job.
     */
    public function show(SmsBroadcastJob $broadcast)
    {
        return view('panel.sms.broadcast.show', compact('broadcast'));
    }

    /**
     * Calculate total recipients based on filters.
     */
    private function calculateRecipients(string $recipientType, array $filters): int
    {
        $query = User::query();

        switch ($recipientType) {
            case 'all':
                // All users except developers
                $query->where('role_level', '>', 0);
                break;
            
            case 'customers':
                // Only customers
                $query->where('role_level', 100);
                break;
            
            case 'operators':
                // Operators and sub-operators
                $query->whereIn('role_level', [30, 40]);
                break;
            
            case 'active_customers':
                // Active customers only
                $query->where('role_level', 100)
                      ->where('is_active', true);
                break;
            
            case 'inactive_customers':
                // Inactive customers
                $query->where('role_level', 100)
                      ->where('is_active', false);
                break;
            
            case 'specific_zone':
                // Customers in specific zone
                if (isset($filters['zone_id'])) {
                    $query->where('role_level', 100)
                          ->where('zone_id', $filters['zone_id']);
                }
                break;
        }

        return $query->count();
    }

    /**
     * Cancel a pending broadcast.
     */
    public function cancel(SmsBroadcastJob $broadcast)
    {
        if ($broadcast->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending broadcasts can be cancelled.']);
        }

        $broadcast->update(['status' => 'cancelled']);

        return redirect()
            ->route('panel.sms.broadcast.index')
            ->with('success', 'Broadcast cancelled successfully.');
    }
}
