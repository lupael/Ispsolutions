<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SmsEvent;
use Illuminate\Http\Request;

class SmsEventController extends Controller
{
    /**
     * Display all SMS events.
     */
    public function index()
    {
        $events = SmsEvent::orderBy('event_name')->get();

        return view('panel.sms.events.index', compact('events'));
    }

    /**
     * Show form to create SMS event.
     */
    public function create()
    {
        $availableEvents = $this->getAvailableEvents();

        return view('panel.sms.events.create', compact('availableEvents'));
    }

    /**
     * Store a new SMS event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string|unique:sms_events,event_name',
            'event_label' => 'required|string|max:255',
            'message_template' => 'required|string|max:1000',
            'is_active' => 'boolean',
        ]);

        SmsEvent::create($request->only([
            'event_name',
            'event_label',
            'message_template',
            'is_active',
        ]));

        return redirect()
            ->route('panel.sms.events.index')
            ->with('success', 'SMS event created successfully.');
    }

    /**
     * Show form to edit SMS event.
     */
    public function edit(SmsEvent $event)
    {
        return view('panel.sms.events.edit', compact('event'));
    }

    /**
     * Update SMS event.
     */
    public function update(Request $request, SmsEvent $event)
    {
        $request->validate([
            'event_label' => 'required|string|max:255',
            'message_template' => 'required|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $event->update($request->only([
            'event_label',
            'message_template',
            'is_active',
        ]));

        return redirect()
            ->route('panel.sms.events.index')
            ->with('success', 'SMS event updated successfully.');
    }

    /**
     * Delete SMS event.
     */
    public function destroy(SmsEvent $event)
    {
        $event->delete();

        return redirect()
            ->route('panel.sms.events.index')
            ->with('success', 'SMS event deleted successfully.');
    }

    /**
     * Get available event types with their variables.
     */
    private function getAvailableEvents(): array
    {
        return [
            'bill_generated' => [
                'label' => 'Bill Generated',
                'variables' => ['customer_name', 'bill_amount', 'due_date', 'invoice_number'],
            ],
            'payment_received' => [
                'label' => 'Payment Received',
                'variables' => ['customer_name', 'payment_amount', 'payment_date', 'receipt_number'],
            ],
            'package_expiring' => [
                'label' => 'Package Expiring Soon',
                'variables' => ['customer_name', 'package_name', 'expiry_date', 'days_remaining'],
            ],
            'package_expired' => [
                'label' => 'Package Expired',
                'variables' => ['customer_name', 'package_name', 'expired_date'],
            ],
            'account_suspended' => [
                'label' => 'Account Suspended',
                'variables' => ['customer_name', 'suspension_reason', 'suspension_date'],
            ],
            'account_activated' => [
                'label' => 'Account Activated',
                'variables' => ['customer_name', 'activation_date', 'package_name'],
            ],
            'welcome_message' => [
                'label' => 'Welcome Message',
                'variables' => ['customer_name', 'username', 'password', 'package_name'],
            ],
            'data_limit_reached' => [
                'label' => 'Data Limit Reached',
                'variables' => ['customer_name', 'data_limit', 'usage', 'reset_date'],
            ],
        ];
    }
}
