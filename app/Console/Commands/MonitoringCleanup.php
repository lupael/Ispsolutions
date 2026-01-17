<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BandwidthUsage;
use App\Models\DeviceMonitor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MonitoringCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:cleanup 
                            {--days=90 : Number of days to keep data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old monitoring and bandwidth usage data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up monitoring data older than {$days} days (before {$cutoffDate->toDateString()})...");

        try {
            // Delete old bandwidth usage data (keep monthly aggregates longer)
            $bandwidthDeleted = BandwidthUsage::where('timestamp', '<', $cutoffDate)
                ->where('period_type', '!=', 'monthly')
                ->delete();

            // Keep monthly data for longer (2 years)
            $monthlyDeleted = BandwidthUsage::where('timestamp', '<', Carbon::now()->subYears(2))
                ->where('period_type', 'monthly')
                ->delete();

            // Clean up old device monitor records (keep last 30 days regardless)
            $monitorDeleted = DeviceMonitor::where('last_check_at', '<', $cutoffDate)
                ->where('last_check_at', '<', Carbon::now()->subDays(30))
                ->delete();

            $totalDeleted = $bandwidthDeleted + $monthlyDeleted + $monitorDeleted;

            $this->info("✓ Cleanup completed:");
            $this->line("  - Bandwidth records: {$bandwidthDeleted}");
            $this->line("  - Monthly aggregates: {$monthlyDeleted}");
            $this->line("  - Monitor records: {$monitorDeleted}");
            $this->line("  - Total: {$totalDeleted}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Cleanup failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
