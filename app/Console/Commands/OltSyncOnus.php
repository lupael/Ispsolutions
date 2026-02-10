<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Console\Command;

class OltSyncOnus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:sync-onus
                            {--olt= : Specific OLT ID to sync}
                            {--force : Force sync even if OLT is not active}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ONUs from OLT devices to database';

    /**
     * Execute the console command.
     */
    public function handle(OltServiceInterface $oltService): int
    {
        $oltId = $this->option('olt');
        $force = $this->option('force');

        $this->info('Syncing ONUs from OLT devices...');

        try {
            if ($oltId) {
                // Sync specific OLT
                $olt = Olt::findOrFail($oltId);

                if (! $olt->isActive() && ! $force) {
                    $this->warn("OLT {$olt->name} is not active. Use --force to sync anyway.");

                    return self::SUCCESS;
                }

                return $this->syncOlt($olt, $oltService);
            }

            // Sync all active OLTs
            $olts = Olt::active()->get();

            if ($olts->isEmpty()) {
                $this->warn('No active OLTs found');

                return self::SUCCESS;
            }

            $summary = [
                'synced' => 0,
                'new' => 0,
                'updated' => 0,
                'failed' => 0,
                'olt_success' => 0,
                'olt_failed' => 0,
            ];

            foreach ($olts as $olt) {
                $this->info("Syncing OLT: {$olt->name}");

                try {
                    $result = $oltService->syncOnus($olt->id);

                    if ($result['synced'] > 0 || $result['failed'] === 0) {
                        $this->info("  ✓ Synced {$result['synced']} ONUs (New: {$result['new']}, Updated: {$result['updated']}, Failed: {$result['failed']})");
                        $summary['olt_success']++;
                        foreach (['synced', 'new', 'updated', 'failed'] as $key) {
                            $summary[$key] += $result[$key];
                        }
                    } else {
                        $this->warn('  - No ONUs found or sync failed');
                        $summary['olt_failed']++;
                    }
                } catch (\Exception $e) {
                    $this->error('  ✗ Error: ' . $e->getMessage());
                    $summary['olt_failed']++;
                }
            }

            $this->newLine();
            $this->info('Sync Summary:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total ONUs Synced', $summary['synced']],
                    ['New ONUs', $summary['new']],
                    ['Updated ONUs', $summary['updated']],
                    ['Failed ONUs', $summary['failed']],
                    ['Successful OLTs', $summary['olt_success']],
                    ['Failed OLTs', $summary['olt_failed']],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during ONU sync: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Sync a specific OLT.
     */
    private function syncOlt(Olt $olt, OltServiceInterface $oltService): int
    {
        $this->info("Syncing ONUs from OLT: {$olt->name}");

        try {
            $result = $oltService->syncOnus($olt->id);

            if ($result['synced'] > 0 || $result['failed'] === 0) {
                $this->info("✓ Successfully synced {$result['synced']} ONUs (New: {$result['new']}, Updated: {$result['updated']})");
                if ($result['failed'] > 0) {
                    $this->warn("  - Failed to sync {$result['failed']} ONUs.");
                }

                return self::SUCCESS;
            }

            $this->warn('No ONUs found or sync failed');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
