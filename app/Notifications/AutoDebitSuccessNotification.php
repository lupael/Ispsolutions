<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\AutoDebitHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Auto-Debit Success Notification
 *
 * Sent to customers when auto-debit payment succeeds
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class AutoDebitSuccessNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Auto-Payment Successful')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your automatic payment has been processed successfully.')
            ->line('Amount: ' . number_format($this->history->amount, 2) . ' BDT')
            ->line('Transaction ID: ' . $this->history->transaction_id)
            ->line('Payment Method: ' . ucfirst($this->history->payment_method ?? 'N/A'))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auto_debit_success',
            'history_id' => $this->history->id,
            'amount' => $this->history->amount,
            'transaction_id' => $this->history->transaction_id,
            'payment_method' => $this->history->payment_method,
        ];
    }
}
