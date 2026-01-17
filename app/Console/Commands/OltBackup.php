<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Console\Command;

class OltBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:backup 
                            {--olt= : Specific OLT ID to backup}
                            {--force : Force backup even if OLT is not active}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create configuration backups for OLT devices';

    /**
     * Execute the console command.
     */
    public function handle(OltServiceInterface $oltService): int
    {
        $oltId = $this->option('olt');
        $force = $this->option('force');

        $this->info('Creating OLT configuration backups...');

        try {
            if ($oltId) {
                // Backup specific OLT
                $olt = Olt::findOrFail($oltId);

                if (! $olt->isActive() && ! $force) {
                    $this->warn("OLT {$olt->name} is not active. Use --force to backup anyway.");

                    return self::SUCCESS;
                }

                return $this->backupOlt($olt, $oltService);
            }

            // Backup all active OLTs
            $olts = Olt::active()->get();

            if ($olts->isEmpty()) {
                $this->warn('No active OLTs found');

                return self::SUCCESS;
            }

            $successCount = 0;
            $failCount = 0;

            foreach ($olts as $olt) {
                $this->info("Backing up OLT: {$olt->name}");

                try {
                    if ($oltService->createBackup($olt->id)) {
                        $this->info('  ✓ Backup created successfully');
                        $successCount++;
                    } else {
                        $this->error('  ✗ Backup failed');
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $this->error('  ✗ Error: ' . $e->getMessage());
                    $failCount++;
                }
            }

            $this->newLine();
            $this->info('Backup Summary:');
            $this->info("Successful Backups: {$successCount}");
            $this->info("Failed Backups: {$failCount}");
            $this->info('Total OLTs: ' . ($successCount + $failCount));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during backup: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Backup a specific OLT.
     */
    private function backupOlt(Olt $olt, OltServiceInterface $oltService): int
    {
        $this->info("Creating backup for OLT: {$olt->name}");

        try {
            if ($oltService->createBackup($olt->id)) {
                $this->info('✓ Backup created successfully');

                // Show backup info
                $backups = $oltService->getBackupList($olt->id);
                if (! empty($backups)) {
                    $latest = $backups[0];
                    $this->info('  File: ' . basename($latest['file_path']));
                    $this->info('  Size: ' . $this->formatBytes($latest['file_size']));
                }

                return self::SUCCESS;
            }

            $this->error('✗ Backup failed');

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Format bytes to human-readable size.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
