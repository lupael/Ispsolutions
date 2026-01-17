<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MonitoringServiceInterface;
use Illuminate\Console\Command;

class MonitoringAggregateDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:aggregate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate hourly bandwidth data to daily summaries';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringServiceInterface $monitoringService): int
    {
        $this->info('Aggregating hourly bandwidth data to daily summaries...');

        try {
            $processed = $monitoringService->aggregateDailyData();
            
            if ($processed > 0) {
                $this->info("✓ Aggregated {$processed} hourly records to daily summaries");
            } else {
                $this->info('No hourly data to aggregate');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Aggregation failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
