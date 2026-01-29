<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OperatorSubscription;
use App\Models\SubscriptionPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Generate Operator Subscription Bills Command
 *
 * Scheduled command to generate subscription bills for platform operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class GenerateOperatorSubscriptionBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operator-subscription:generate-bills {--subscription_id= : Generate bill for a specific subscription} {--dry-run : Simulate billing without actually creating bills}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate subscription bills for operators whose billing is due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting operator subscription billing process...');

        $dryRun = $this->option('dry-run');
        $subscriptionId = $this->option('subscription_id');

        // Build query for subscriptions due for billing
        $query = OperatorSubscription::with('plan', 'operator')
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now());

        // Filter by specific subscription if provided
        if ($subscriptionId) {
            $query->where('id', $subscriptionId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->warn('No operator subscriptions found that are due for billing.');
            return self::SUCCESS;
        }

        $this->info("Found {$subscriptions->count()} operator subscription(s) due for billing.");

        $generated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Check if subscription is expired
                if ($subscription->isExpired()) {
                    $this->line("Skipping subscription #{$subscription->id} - Expired");
                    $subscription->markExpired();
                    $skipped++;
                    continue;
                }

                // Check if there's already a pending payment for this billing period
                $billingWindowStart = $subscription->next_billing_date->copy()->subDay();
                $billingWindowEnd = $subscription->next_billing_date->copy()->addDay();

                $existingPayment = SubscriptionPayment::where('operator_subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->whereBetween('billing_period_start', [$billingWindowStart, $billingWindowEnd])
                    ->first();

                if ($existingPayment) {
                    $this->line("Skipping subscription #{$subscription->id} - Pending payment already exists");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("DRY RUN: Would generate bill for subscription #{$subscription->id} (Operator: {$subscription->operator->name})");
                    $generated++;
                } else {
                    // Calculate billing period
                    $startDate = $subscription->next_billing_date;
                    $endDate = $startDate->copy()->addMonths($subscription->billing_cycle);
                    
                    // Calculate amount
                    $amount = $subscription->plan->price * $subscription->billing_cycle;

                    // Create payment record
                    $payment = SubscriptionPayment::create([
                        'operator_subscription_id' => $subscription->id,
                        'operator_id' => $subscription->operator_id,
                        'amount' => $amount,
                        'status' => 'pending',
                        'billing_period_start' => $startDate,
                        'billing_period_end' => $endDate,
                    ]);

                    // Generate invoice number
                    $payment->generateInvoiceNumber();

                    // Update subscription next billing date
                    $subscription->update([
                        'next_billing_date' => $endDate,
                    ]);

                    $this->info("✓ Generated bill for subscription #{$subscription->id} (Invoice: {$payment->invoice_number})");
                    
                    Log::info('Operator subscription bill generated', [
                        'subscription_id' => $subscription->id,
                        'payment_id' => $payment->id,
                        'operator_id' => $subscription->operator_id,
                        'amount' => $amount,
                    ]);

                    // TODO: Send notification to operator about new bill
                    
                    $generated++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to generate bill for subscription #{$subscription->id}: {$e->getMessage()}");
                
                Log::error('Operator subscription bill generation failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Operator subscription billing process complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Due Subscriptions', $subscriptions->count()],
                ['Bills Generated', $generated],
                ['Skipped', $skipped],
                ['Failed', $failed],
            ]
        );

        return self::SUCCESS;
    }
}
