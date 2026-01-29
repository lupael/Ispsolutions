<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SmsBalanceLowNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Check SMS Balance Command
 *
 * Checks operator SMS balances and sends notifications for low balances
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class CheckSmsBalanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:check-balance
                          {--force : Force notification even if already notified today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check operator SMS balances and send low balance notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking SMS balances for operators...');

        // Get all operators and sub-operators with SMS features enabled
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['operator', 'sub-operator']);
        })
            ->whereNotNull('sms_balance')
            ->whereNotNull('sms_low_balance_threshold')
            ->get();

        $notifiedCount = 0;
        $lowBalanceCount = 0;

        foreach ($users as $user) {
            // Check if balance is below threshold
            if ($user->hasLowSmsBalance()) {
                $lowBalanceCount++;

                // Check if we've already notified today (unless --force is used)
                $shouldNotify = $this->option('force') || $this->shouldNotifyUser($user);

                if ($shouldNotify) {
                    try {
                        $user->notify(new SmsBalanceLowNotification(
                            $user->sms_balance ?? 0,
                            $user->sms_low_balance_threshold ?? 100
                        ));

                        // Update last notification timestamp
                        $user->update(['sms_low_balance_notified_at' => now()]);

                        $notifiedCount++;

                        $this->info("✓ Notified {$user->name} (ID: {$user->id}) - Balance: {$user->sms_balance}");
                    } catch (\Exception $e) {
                        Log::error('Failed to send SMS low balance notification', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                        $this->error("✗ Failed to notify {$user->name} (ID: {$user->id})");
                    }
                } else {
                    $this->line("- Skipped {$user->name} (ID: {$user->id}) - Already notified today");
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Total operators checked: {$users->count()}");
        $this->info("- Operators with low balance: {$lowBalanceCount}");
        $this->info("- Notifications sent: {$notifiedCount}");

        return Command::SUCCESS;
    }

    /**
     * Check if user should be notified (not notified in last 24 hours)
     */
    protected function shouldNotifyUser(User $user): bool
    {
        if (! $user->sms_low_balance_notified_at) {
            return true;
        }

        // Only notify once per day
        return $user->sms_low_balance_notified_at->diffInHours(now()) >= 24;
    }
}
