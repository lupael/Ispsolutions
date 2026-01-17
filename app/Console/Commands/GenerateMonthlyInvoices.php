<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-monthly {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly recurring invoices for PPPoE users';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        if (!$this->option('force') && !$this->confirm('Do you want to generate monthly invoices?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Generating monthly invoices...');

        // Find users with monthly billing packages
        $users = User::whereHas('package', function ($query) {
            $query->where('billing_type', 'monthly')
                ->where('is_active', true);
        })
        ->where('is_active', true)
        ->get();

        $count = 0;
        foreach ($users as $user) {
            // Check if user already has an invoice for current month
            $hasInvoiceThisMonth = $user->invoices()
                ->whereYear('billing_period_start', now()->year)
                ->whereMonth('billing_period_start', now()->month)
                ->exists();

            if (!$hasInvoiceThisMonth) {
                $package = $user->package;
                if ($package) {
                    $billingService->generateMonthlyInvoice($user, $package);
                    $count++;
                }
            }
        }

        $this->info("Generated {$count} monthly invoice(s).");

        return Command::SUCCESS;
    }
}
