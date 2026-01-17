<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class LockExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:lock-expired {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock accounts with expired/overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        if (!$this->option('force') && !$this->confirm('Do you want to lock expired accounts?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Locking expired accounts...');

        $count = $billingService->lockExpiredAccounts();

        $this->info("Locked {$count} expired account(s).");

        return Command::SUCCESS;
    }
}
