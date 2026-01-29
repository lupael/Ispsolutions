<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessAutoDebitJob;
use App\Models\SubscriptionBill;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process Auto-Debit Payments Command
 *
 * Scheduled command to process auto-debit payments for customers
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class ProcessAutoDebitPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-debit:process 
                            {--customer_id= : Process auto-debit for a specific customer}
                            {--dry-run : Simulate processing without actually dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auto-debit payments for eligible customers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting auto-debit payment processing...');

        $dryRun = $this->option('dry-run');
        $customerId = $this->option('customer_id');

        // Build query for eligible customers
        $query = User::where('auto_debit_enabled', true)
            ->whereColumn('auto_debit_retry_count', '<', 'auto_debit_max_retries');

        // Filter by specific customer if provided
        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();

        if ($customers->isEmpty()) {
            $this->warn('No eligible customers found for auto-debit processing.');
            return self::SUCCESS;
        }

        $this->info("Found {$customers->count()} eligible customer(s) for auto-debit processing.");

        $processed = 0;
        $skipped = 0;

        foreach ($customers as $customer) {
            // Check if customer has a due bill or needs to be charged
            if (! $this->shouldProcessCustomer($customer)) {
                $this->line("Skipping customer #{$customer->id} - No pending bills");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("DRY RUN: Would process auto-debit for customer #{$customer->id} ({$customer->name})");
                $processed++;
            } else {
                try {
                    // Dispatch job for this customer
                    ProcessAutoDebitJob::dispatch($customer);
                    $this->info("✓ Dispatched auto-debit job for customer #{$customer->id} ({$customer->name})");
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("✗ Failed to dispatch job for customer #{$customer->id}: {$e->getMessage()}");
                    Log::error('Auto-debit job dispatch failed', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info("Auto-debit processing complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Eligible Customers', $customers->count()],
                ['Processed', $processed],
                ['Skipped', $skipped],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Determine if customer should be processed for auto-debit
     *
     * @param User $customer
     * @return bool
     */
    protected function shouldProcessCustomer(User $customer): bool
    {
        // Check if customer has unpaid bills that are due
        $hasDueBills = SubscriptionBill::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->where('due_date', '<=', now())
            ->exists();

        if (! $hasDueBills) {
            return false;
        }

        // Check if customer hasn't been charged today already
        $lastAttemptToday = $customer->auto_debit_last_attempt 
            && $customer->auto_debit_last_attempt->isToday();

        return ! $lastAttemptToday;
    }
}
