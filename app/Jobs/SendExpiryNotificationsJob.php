<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendExpiryNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SmsService $smsService)
    {
        $users = User::where('is_subscriber', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(7))
            ->get();

        foreach ($users as $user) {
            $daysUntilExpiry = now()->diffInDays($user->expires_at);
            $smsService->sendInvoiceExpiringSoonSms($user->invoices()->latest()->first(), $daysUntilExpiry);
        }
    }
}
