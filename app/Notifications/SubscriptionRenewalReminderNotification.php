<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Renewal Reminder Notification
 *
 * Notifies operators when their subscription is about to expire
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class SubscriptionRenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
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
        $planName = $this->subscription->plan->name ?? 'Your Plan';
        
        return (new MailMessage)
            ->warning()
            ->subject('Subscription Renewal Reminder - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your subscription to **' . $planName . '** will expire in ' . $this->daysRemaining . ' day(s).')
            ->line('**Subscription Details:**')
            ->line('Plan: ' . $planName)
            ->line('Expires on: ' . $this->subscription->end_date->format('F j, Y'))
            ->line('Renewal Amount: ' . $this->subscription->amount . ' ' . $this->subscription->currency)
            ->line('Please renew your subscription to continue enjoying uninterrupted service.')
            ->action('Renew Subscription', route('panel.operator.subscriptions.index'))
            ->line('Thank you for being a valued customer!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_renewal_reminder',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? 'N/A',
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $this->subscription->end_date->toDateTimeString(),
            'amount' => $this->subscription->amount,
            'currency' => $this->subscription->currency,
            'message' => 'Your subscription expires in ' . $this->daysRemaining . ' day(s). Please renew to continue service.',
        ];
    }
}
