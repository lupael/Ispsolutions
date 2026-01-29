<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionRenewalReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Send Subscription Renewal Reminders Command
 *
 * Sends renewal reminders to operators whose subscriptions are expiring soon
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class SendSubscriptionRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:send-reminders
                          {--days=7 : Days before expiration to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminders for expiring subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysAhead = (int) $this->option('days');
        $this->info("Checking for subscriptions expiring in {$daysAhead} days...");

        // Get active subscriptions expiring soon
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->whereBetween('end_date', [
                now()->addDays($daysAhead)->startOfDay(),
                now()->addDays($daysAhead)->endOfDay(),
            ])
            ->with('tenant')
            ->get();

        $sentCount = 0;

        foreach ($expiringSubscriptions as $subscription) {
            try {
                // Get tenant owner (operator)
                $operator = $subscription->tenant->owner ?? null;

                if (! $operator) {
                    $this->warn("✗ Subscription ID {$subscription->id} has no owner");
                    continue;
                }

                // Send notification
                $operator->notify(new SubscriptionRenewalReminderNotification(
                    $subscription,
                    $daysAhead
                ));

                $sentCount++;

                $this->info("✓ Sent reminder to {$operator->name} (Subscription ID: {$subscription->id})");
            } catch (\Exception $e) {
                Log::error('Failed to send subscription renewal reminder', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("✗ Failed to send reminder for Subscription ID {$subscription->id}");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Subscriptions expiring in {$daysAhead} days: {$expiringSubscriptions->count()}");
        $this->info("- Reminders sent: {$sentCount}");

        return Command::SUCCESS;
    }
}
