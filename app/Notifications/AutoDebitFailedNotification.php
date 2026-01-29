<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\AutoDebitHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Auto-Debit Failed Notification
 *
 * Sent to customers when auto-debit payment fails
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class AutoDebitFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected AutoDebitHistory $history
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
        $message = (new MailMessage)
            ->subject('Auto-Payment Failed - Action Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We were unable to process your automatic payment.')
            ->line('Amount: ' . number_format($this->history->amount, 2) . ' BDT')
            ->line('Reason: ' . ($this->history->failure_reason ?? 'Payment processing failed'))
            ->line('Retry Attempt: ' . $this->history->retry_count . ' of ' . $notifiable->auto_debit_max_retries);

        // Check if max retries reached
        if ($this->history->retry_count >= $notifiable->auto_debit_max_retries) {
            $message->line('⚠️ Auto-payment has been disabled after maximum retry attempts.')
                ->line('Please update your payment method or make a manual payment to continue your service.')
                ->action('Make Payment', route('panel.customer.payments.create'));
        } else {
            $message->line('We will automatically retry the payment.')
                ->line('To avoid service interruption, please ensure your payment method is valid.')
                ->action('Update Payment Method', route('panel.customer.profile.edit'));
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auto_debit_failed',
            'history_id' => $this->history->id,
            'amount' => $this->history->amount,
            'failure_reason' => $this->history->failure_reason,
            'retry_count' => $this->history->retry_count,
            'max_retries' => $notifiable->auto_debit_max_retries,
        ];
    }
}
