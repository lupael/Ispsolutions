<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\FindsAssociatedModel;
use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Console\Command;

class OltHealthCheck extends Command
{
    use FindsAssociatedModel;

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
                /** @var Olt $olt */
                $olt = $this->findModel(Olt::class, (string) $oltId);
                $this->info("Checking OLT: {$olt->name} ({$olt->ip_address})");
                $isHealthy = $this->checkAndReportOlt($olt, $oltService, $details);

                return $isHealthy ? self::SUCCESS : self::FAILURE;
            }

            // Check all active OLTs
            $olts = Olt::active()->get();

            if ($olts->isEmpty()) {
                $this->warn('No active OLTs found');

                return self::SUCCESS;
            }

            $this->info("Checking {$olts->count()} OLT(s)...");
            $this->newLine();

            $healthy = 0;
            $unhealthy = 0;

            foreach ($olts as $olt) {
                if ($this->checkAndReportOlt($olt, $oltService, $details)) {
                    $healthy++;
                } else {
                    $unhealthy++;
                }
            }

            $this->newLine();
            $this->info('Health Check Summary:');
            $this->info("  Healthy OLTs: {$healthy}");
            if ($unhealthy > 0) {
                $this->warn("  Unhealthy OLTs: {$unhealthy}");
            }
            $this->info('  Total OLTs: ' . ($healthy + $unhealthy));

            return $unhealthy > 0 ? self::FAILURE : self::SUCCESS;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->error("OLT with ID '{$oltId}' not found.");

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Error during health check: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Check a single OLT and report its status.
     */
    private function checkAndReportOlt(Olt $olt, OltServiceInterface $oltService, bool $details): bool
    {
        $result = $oltService->testConnection($olt);

        if ($result['success']) {
            $this->info("✓ {$olt->name} ({$olt->ip_address}) - Healthy (Latency: {$result['latency']}ms)");

            $olt->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

            if ($details) {
                $stats = $oltService->getOltStatistics($olt);
                $this->line("  Statistics: Total ONUs: {$stats['total_onus']}, Online: {$stats['online_onus']}, Offline: {$stats['offline_onus']}");
            }

            return true;
        }

        $this->error("✗ {$olt->name} ({$olt->ip_address}) - Unhealthy: {$result['message']}");

        // Update health status
        $olt->update(['health_status' => 'unhealthy', 'last_health_check_at' => now()]);

        return false;
    }
}
