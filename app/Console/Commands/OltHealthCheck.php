<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Console\Command;

class OltHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:health-check 
                            {--olt= : Specific OLT ID to check}
                            {--details : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check connectivity and health of OLT devices';

    /**
     * Execute the console command.
     */
    public function handle(OltServiceInterface $oltService): int
    {
        $oltId = $this->option('olt');
        $details = $this->option('details');

        $this->info('Checking OLT health...');

        try {
            if ($oltId) {
                // Check specific OLT
                $olt = Olt::findOrFail($oltId);

                return $this->checkOlt($olt, $oltService, $details);
            }

            // Check all active OLTs
            $olts = Olt::active()->get();

            if ($olts->isEmpty()) {
                $this->warn('No active OLTs found');

                return self::SUCCESS;
            }

            $healthy = 0;
            $unhealthy = 0;

            foreach ($olts as $olt) {
                $result = $oltService->testConnection($olt->id);

                if ($result['success']) {
                    $healthy++;
                    if ($details) {
                        $this->info("✓ {$olt->name} ({$olt->ip_address}) - Healthy (Latency: {$result['latency']}ms)");
                    }
                } else {
                    $unhealthy++;
                    $this->error("✗ {$olt->name} ({$olt->ip_address}) - Unhealthy: {$result['message']}");

                    // Update health status
                    $olt->update([
                        'health_status' => 'unhealthy',
                        'last_health_check_at' => now(),
                    ]);
                }
            }

            $this->newLine();
            $this->info('Health Check Summary:');
            $this->info("Healthy OLTs: {$healthy}");
            $this->info("Unhealthy OLTs: {$unhealthy}");
            $this->info('Total OLTs: ' . ($healthy + $unhealthy));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during health check: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Check a specific OLT.
     */
    private function checkOlt(Olt $olt, OltServiceInterface $oltService, bool $details): int
    {
        $this->info("Checking OLT: {$olt->name} ({$olt->ip_address})");

        $result = $oltService->testConnection($olt->id);

        if ($result['success']) {
            $this->info('✓ Connection successful');
            $this->info("  Latency: {$result['latency']}ms");

            if ($details) {
                $stats = $oltService->getOltStatistics($olt->id);
                $this->newLine();
                $this->info('Statistics:');
                $this->info("  Total ONUs: {$stats['total_onus']}");
                $this->info("  Online ONUs: {$stats['online_onus']}");
                $this->info("  Offline ONUs: {$stats['offline_onus']}");
            }

            return self::SUCCESS;
        }

        $this->error("✗ Connection failed: {$result['message']}");

        // Update health status
        $olt->update([
            'health_status' => 'unhealthy',
            'last_health_check_at' => now(),
        ]);

        return self::FAILURE;
    }
}
