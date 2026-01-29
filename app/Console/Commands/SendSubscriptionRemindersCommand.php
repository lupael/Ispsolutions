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
                          {--days=* : Days before expiration to send reminders (e.g., 7,3,1)}';

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
        // Get days from options, default to 7, 3, 1 if not specified
        $daysOptions = $this->option('days');
        $daysToCheck = !empty($daysOptions) ? array_map('intval', $daysOptions) : [7, 3, 1];

        $this->info("Checking for subscriptions expiring in: " . implode(', ', $daysToCheck) . " days...");

        $totalSent = 0;
        $totalSubscriptions = 0;

        foreach ($daysToCheck as $daysAhead) {
            // Get active subscriptions expiring on this specific day
            $expiringSubscriptions = Subscription::where('status', 'active')
                ->whereBetween('end_date', [
                    now()->addDays($daysAhead)->startOfDay(),
                    now()->addDays($daysAhead)->endOfDay(),
                ])
                ->with('tenant.owner')
                ->get();

            if ($expiringSubscriptions->isEmpty()) {
                $this->line("No subscriptions expiring in {$daysAhead} days");
                continue;
            }

            $this->info("Found {$expiringSubscriptions->count()} subscription(s) expiring in {$daysAhead} days");
            $totalSubscriptions += $expiringSubscriptions->count();

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
                    $totalSent++;

                    $this->info("✓ Sent {$daysAhead}-day reminder to {$operator->name} (Subscription ID: {$subscription->id})");
                } catch (\Exception $e) {
                    Log::error('Failed to send subscription renewal reminder', [
                        'subscription_id' => $subscription->id,
                        'days_ahead' => $daysAhead,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("✗ Failed to send reminder for Subscription ID {$subscription->id}");
                }
            }

            $this->info("Sent {$sentCount} reminder(s) for subscriptions expiring in {$daysAhead} days");
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Total subscriptions found: {$totalSubscriptions}");
        $this->info("- Total reminders sent: {$totalSent}");

        return Command::SUCCESS;
    }
}
