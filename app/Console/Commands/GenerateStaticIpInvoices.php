<?php

namespace App\Console\Commands;

use App\Services\StaticIpBillingService;
use Illuminate\Console\Command;

class GenerateStaticIpInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-static-ip {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for static IP allocations';

    /**
     * Execute the console command.
     */
    public function handle(StaticIpBillingService $staticIpBillingService): int
    {
        if (!$this->option('force') && !$this->confirm('Do you want to generate static IP invoices?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Generating static IP monthly invoices...');

        $count = $staticIpBillingService->generateMonthlyInvoicesForStaticIPs();

        $this->info("Generated {$count} static IP invoice(s).");

        return Command::SUCCESS;
    }
}
