<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SmsPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SMS Payment Success Notification
 *
 * Notifies operators when their SMS payment is successfully processed
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class SmsPaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SmsPayment $payment
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
            ->subject('SMS Payment Successful - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your SMS payment has been processed successfully!')
            ->line('**Payment Details:**')
            ->line('Transaction ID: ' . $this->payment->transaction_id)
            ->line('Amount: ' . $this->payment->amount . ' ' . config('app.currency', 'BDT'))
            ->line('SMS Credits: ' . number_format($this->payment->sms_quantity))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Your new SMS balance is: ' . number_format($notifiable->sms_balance ?? 0))
            ->action('View Payment Details', route('panel.operator.sms-payments.index'))
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
            'type' => 'sms_payment_success',
            'payment_id' => $this->payment->id,
            'transaction_id' => $this->payment->transaction_id,
            'amount' => $this->payment->amount,
            'sms_quantity' => $this->payment->sms_quantity,
            'payment_method' => $this->payment->payment_method,
            'new_balance' => $notifiable->sms_balance ?? 0,
            'message' => 'SMS payment of ' . $this->payment->amount . ' ' . config('app.currency', 'BDT') . ' successful. ' . number_format($this->payment->sms_quantity) . ' SMS credits added.',
        ];
    }
}
