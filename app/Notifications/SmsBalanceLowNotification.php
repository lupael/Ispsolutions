<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SMS Balance Low Notification
 *
 * Notifies operators when their SMS balance is running low
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class SmsBalanceLowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $currentBalance,
        public int $threshold
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
        return (new MailMessage)
            ->warning()
            ->subject('Low SMS Balance Alert - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your SMS balance is running low!')
            ->line('**Current Balance:** ' . number_format($this->currentBalance) . ' SMS')
            ->line('**Threshold:** ' . number_format($this->threshold) . ' SMS')
            ->line('To ensure uninterrupted service, please top up your SMS credits.')
            ->action('Buy SMS Credits', route('panel.operator.sms-payments.create'))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sms_balance_low',
            'current_balance' => $this->currentBalance,
            'threshold' => $this->threshold,
            'message' => 'Your SMS balance (' . number_format($this->currentBalance) . ') is below the threshold (' . number_format($this->threshold) . '). Please top up.',
        ];
    }
}
