<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SubscriptionBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Payment Due Notification
 *
 * Notifies operators when their subscription payment is due
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class SubscriptionPaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SubscriptionBill $bill
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subscription = $this->bill->subscription;
        $planName = $subscription->plan->name ?? 'Your Plan';
        $daysUntilDue = now()->diffInDays($this->bill->due_date, false);
        
        return (new MailMessage)
            ->warning()
            ->subject('Subscription Payment Due - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your subscription payment is due.')
            ->line('**Bill Details:**')
            ->line('Plan: ' . $planName)
            ->line('Amount Due: ' . $this->bill->amount . ' ' . $this->bill->currency)
            ->line('Due Date: ' . $this->bill->due_date->format('F j, Y'))
            ->line('Days Until Due: ' . max(0, $daysUntilDue) . ' day(s)')
            ->line('Please make your payment before the due date to avoid service interruption.')
            ->action('Pay Now', route('panel.operator.subscriptions.bills'))
            ->line('Thank you for your prompt attention!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $daysUntilDue = now()->diffInDays($this->bill->due_date, false);
        
        return [
            'type' => 'subscription_payment_due',
            'bill_id' => $this->bill->id,
            'subscription_id' => $this->bill->subscription_id,
            'amount' => $this->bill->amount,
            'currency' => $this->bill->currency,
            'due_date' => $this->bill->due_date->toDateTimeString(),
            'days_until_due' => max(0, $daysUntilDue),
            'message' => 'Subscription payment of ' . $this->bill->amount . ' ' . $this->bill->currency . ' is due on ' . $this->bill->due_date->format('M j, Y') . '.',
        ];
    }
}
