<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PackageFup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FUP Warning Notification
 *
 * Notifies customers when they are approaching their FUP limit (80-99% usage)
 */
class FupWarningNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->warning()
            ->subject('Fair Usage Policy Warning - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You are approaching your Fair Usage Policy (FUP) limit.')
            ->line('**Current Usage:** ' . implode(', ', $usageInfo))
            ->line('Please be aware that if you exceed 100% of your FUP limit, your internet speed may be reduced.')
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
            'type' => 'fup_warning',
            'data_percent' => $this->dataPercent,
            'time_percent' => $this->timePercent,
            'message' => 'You are approaching your Fair Usage Policy limit. Current usage: ' . 
                         number_format(max($this->dataPercent, $this->timePercent), 1) . '%',
        ];
    }
}
