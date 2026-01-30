<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PackageFup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FUP Reset Notification
 *
 * Notifies customers when their FUP has been reset and speed restored
 */
class FupResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PackageFup $fup
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
            ->success()
            ->subject('Fair Usage Policy Reset - Speed Restored')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your Fair Usage Policy (FUP) has been reset.')
            ->line('Your internet speed has been restored to normal.')
            ->line('Your usage counters have been reset to zero.')
            ->action('View Account', url('/'))
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
            'type' => 'fup_reset',
            'message' => 'Your Fair Usage Policy has been reset and your speed has been restored.',
        ];
    }
}
