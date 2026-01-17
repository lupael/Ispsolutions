<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateDailyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-daily {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily invoices for PPPoE users with daily billing';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        if (!$this->option('force') && !$this->confirm('Do you want to generate daily invoices?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Generating daily invoices...');

        // Find users with daily billing packages that need invoicing
        $users = User::whereHas('package', function ($query) {
            $query->where('billing_type', 'daily')
                ->where('is_active', true);
        })
        ->where('is_active', true)
        ->whereDoesntHave('invoices', function ($query) {
            $query->where('status', 'pending')
                ->whereDate('billing_period_start', today());
        })
        ->get();

        $count = 0;
        foreach ($users as $user) {
            $package = $user->package;
            if ($package) {
                $validityDays = $package->validity_days ?? 1;
                $billingService->generateDailyInvoice($user, $package, $validityDays);
                $count++;
            }
        }

        $this->info("Generated {$count} daily invoice(s).");

        return Command::SUCCESS;
    }
}
