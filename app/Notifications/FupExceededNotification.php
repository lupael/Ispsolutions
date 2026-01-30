<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PackageFup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FUP Exceeded Notification
 *
 * Notifies customers when they have exceeded their FUP limit (100%+ usage)
 */
class FupExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PackageFup $fup,
        public int $usedBytes,
        public int $usedMinutes,
        public float $dataPercent,
        public float $timePercent
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
        $usageInfo = [];
        
        if ($this->fup->data_limit_bytes) {
            $usageInfo[] = 'Data: ' . number_format($this->dataPercent, 1) . '%';
        }
        
        if ($this->fup->time_limit_minutes) {
            $usageInfo[] = 'Time: ' . number_format($this->timePercent, 1) . '%';
        }

        $message = (new MailMessage)
            ->error()
            ->subject('Fair Usage Policy Exceeded - Speed Reduced')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have exceeded your Fair Usage Policy (FUP) limit.')
            ->line('**Current Usage:** ' . implode(', ', $usageInfo));

        if ($this->fup->reduced_speed) {
            $message->line('**Your internet speed has been reduced to:** ' . $this->fup->reduced_speed);
        }

        $message->line('Your speed will be restored at the next billing cycle reset.')
            ->action('View Account', url('/'))
            ->line('Thank you for understanding!');

        return $message;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fup_exceeded',
            'data_percent' => $this->dataPercent,
            'time_percent' => $this->timePercent,
            'reduced_speed' => $this->fup->reduced_speed,
            'message' => 'You have exceeded your Fair Usage Policy limit. Your speed has been reduced.',
        ];
    }
}
