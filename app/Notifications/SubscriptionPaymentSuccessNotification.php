<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SubscriptionBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Payment Success Notification
 *
 * Notifies operators when their subscription payment is successful
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class SubscriptionPaymentSuccessNotification extends Notification implements ShouldQueue
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
        
        $mailMessage = (new MailMessage)
            ->success()
            ->subject('Subscription Payment Received - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you! Your subscription payment has been received successfully.')
            ->line('**Payment Details:**')
            ->line('Plan: ' . $planName)
            ->line('Amount Paid: ' . $this->bill->amount . ' ' . $this->bill->currency)
            ->line('Billing Period: ' . $this->bill->billing_period_start->format('M j, Y') . ' - ' . $this->bill->billing_period_end->format('M j, Y'));

        // Only add payment date if paid_at is set
        if ($this->bill->paid_at) {
            $mailMessage->line('Payment Date: ' . $this->bill->paid_at->format('F j, Y'));
        }

        $mailMessage
            ->line('Your subscription has been renewed and will remain active until ' . $subscription->end_date->format('F j, Y'))
            ->action('View Invoice', route('panel.operator.subscriptions.bills'))
            ->line('Thank you for your continued support!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_payment_success',
            'bill_id' => $this->bill->id,
            'subscription_id' => $this->bill->subscription_id,
            'amount' => $this->bill->amount,
            'currency' => $this->bill->currency,
            'paid_at' => $this->bill->paid_at->toDateTimeString(),
            'message' => 'Subscription payment of ' . $this->bill->amount . ' ' . $this->bill->currency . ' received successfully.',
        ];
    }
}
