<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SmsPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SMS Payment Failed Notification
 *
 * Notifies operators when their SMS payment fails
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class SmsPaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SmsPayment $payment,
        public string $failureReason
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
            ->error()
            ->subject('SMS Payment Failed - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your SMS payment could not be processed.')
            ->line('**Payment Details:**')
            ->line('Amount: ' . $this->payment->amount . ' ' . config('app.currency', 'BDT'))
            ->line('SMS Credits: ' . number_format($this->payment->sms_quantity))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('**Failure Reason:**')
            ->line($this->failureReason)
            ->action('Try Again', route('panel.operator.sms-payments.create'))
            ->line('If you continue to experience issues, please contact our support team.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sms_payment_failed',
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'sms_quantity' => $this->payment->sms_quantity,
            'payment_method' => $this->payment->payment_method,
            'failure_reason' => $this->failureReason,
            'message' => 'SMS payment of ' . $this->payment->amount . ' ' . config('app.currency', 'BDT') . ' failed. Reason: ' . $this->failureReason,
        ];
    }
}
