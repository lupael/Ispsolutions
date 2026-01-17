<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MonitoringServiceInterface;
use Illuminate\Console\Command;

class MonitoringAggregateHourly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:aggregate-hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate raw bandwidth data to hourly summaries';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringServiceInterface $monitoringService): int
    {
        $this->info('Aggregating raw bandwidth data to hourly summaries...');

        try {
            $processed = $monitoringService->aggregateHourlyData();
            
            if ($processed > 0) {
                $this->info("✓ Aggregated {$processed} raw records to hourly summaries");
            } else {
                $this->info('No raw data to aggregate');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Aggregation failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
