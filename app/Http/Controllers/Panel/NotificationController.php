<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display notification center.
     */
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('panels.shared.notifications.index', compact('notifications'));
    }

    /**
     * Show notification preferences.
     */
    public function preferences(): View
    {
        return view('panels.shared.notifications.preferences');
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_invoice_generated' => 'nullable|boolean',
            'email_payment_received' => 'nullable|boolean',
            'email_invoice_overdue' => 'nullable|boolean',
            'email_subscription_renewal' => 'nullable|boolean',
            'sms_invoice_generated' => 'nullable|boolean',
            'sms_payment_received' => 'nullable|boolean',
            'sms_invoice_overdue' => 'nullable|boolean',
            'inapp_all' => 'nullable|boolean',
        ]);

        // Store preferences in user's settings (could be a separate preferences table or user meta)
        // For now, store as JSON in a potential user settings field or create preferences table
        $user = auth()->user();

        // You could store this in a dedicated preferences table or user settings column
        // Example: $user->update(['notification_preferences' => $validated]);
        // For now, we'll use session/cache as a demonstration
        cache()->put("notification_preferences_{$user->id}", $validated, now()->addYear());

        return redirect()
            ->route('notifications.preferences')
            ->with('success', 'Notification preferences updated successfully!');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
