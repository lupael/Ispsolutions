<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Console\Command;

class PayPendingCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:pay-pending {--threshold=100 : Minimum commission amount to auto-pay} {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically pay pending commissions that meet the threshold';

    /**
     * Execute the console command.
     */
    public function handle(CommissionService $commissionService): int
    {
        $threshold = (float) $this->option('threshold');

        if (!$this->option('force') && !$this->confirm("Do you want to auto-pay commissions >= {$threshold}?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Processing pending commissions...');

        // Get resellers with pending commissions >= threshold
        $resellerCommissions = Commission::where('status', 'pending')
            ->selectRaw('reseller_id, SUM(commission_amount) as total')
            ->groupBy('reseller_id')
            ->having('total', '>=', $threshold)
            ->get();

        $count = 0;
        $totalPaid = 0;

        foreach ($resellerCommissions as $resellerData) {
            // Get all pending commissions for this reseller
            $commissions = Commission::where('reseller_id', $resellerData->reseller_id)
                ->where('status', 'pending')
                ->get();

            foreach ($commissions as $commission) {
                $commissionService->payCommission($commission, [
                    'notes' => 'Auto-paid via scheduled command',
                ]);
                $count++;
                $totalPaid += $commission->commission_amount;
            }
        }

        $this->info("Paid {$count} commission(s) totaling {$totalPaid}.");

        return Command::SUCCESS;
    }
}
