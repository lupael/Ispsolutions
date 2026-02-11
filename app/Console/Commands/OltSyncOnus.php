<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\FindsAssociatedModel;
use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Console\Command;

class OltSyncOnus extends Command
{
    use FindsAssociatedModel;

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
                /** @var Olt $olt */
                $olt = $this->findModel(Olt::class, (string) $oltId);

                if (! $olt->isActive() && ! $force) {
                    $this->warn("OLT {$olt->name} is not active. Use --force to sync anyway.");

                    return self::SUCCESS;
                }
                $result = $this->syncOlt($olt, $oltService);

                return $result['success'] ? self::SUCCESS : self::FAILURE;
            }

            // Sync all active OLTs
            $olts = Olt::active()->get();

            if ($olts->isEmpty()) {
                $this->warn('No active OLTs found');

                return self::SUCCESS;
            }

            $summary = ['synced' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0];
            $oltSuccessCount = 0;
            $oltFailCount = 0;

            foreach ($olts as $olt) {
                $result = $this->syncOlt($olt, $oltService);
                if ($result['success']) {
                    $oltSuccessCount++;
                    foreach (array_keys($summary) as $key) {
                        $summary[$key] += $result[$key];
                    }
                } else {
                    $oltFailCount++;
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
                    ['Successful OLTs', $oltSuccessCount],
                    ['Failed OLTs', $oltFailCount],
                ]
            );

            return $oltFailCount > 0 ? self::FAILURE : self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during ONU sync: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Sync a specific OLT.
     */
    private function syncOlt(Olt $olt, OltServiceInterface $oltService): array
    {
        $this->info("Syncing ONUs from OLT: {$olt->name}");

        try {
            $result = $oltService->syncOnus($olt->id);

            if ($result['synced'] === 0 && $result['new'] === 0 && $result['updated'] === 0) {
                $this->line("  - No new or updated ONUs found.");
            } else {
                $this->info("  ✓ Synced {$result['synced']} ONUs (New: {$result['new']}, Updated: {$result['updated']})");
            }

            if ($result['failed'] > 0) {
                $this->warn("  - Failed to sync {$result['failed']} ONUs.");
            }

            $result['success'] = true;

            return $result;
        } catch (\Exception $e) {
            $this->error('  ✗ Error: ' . $e->getMessage());

            return ['success' => false, 'synced' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0];
        }
    }
}
