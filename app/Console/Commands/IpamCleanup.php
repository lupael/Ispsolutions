<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\IpamServiceInterface;
use Illuminate\Console\Command;

class IpamCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipam:cleanup 
                            {--days=30 : Number of days after which to clean up expired allocations}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired IP allocations and old allocation history';

    /**
     * Execute the console command.
     */
    public function handle(IpamServiceInterface $ipamService): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info('Starting IPAM cleanup...');
        $this->info("Cleaning up allocations older than {$days} days");

        if (! $force && ! $this->confirm('Do you want to continue?', true)) {
            $this->info('Cleanup cancelled.');

            return Command::SUCCESS;
        }

        try {
            $result = $ipamService->cleanupExpiredAllocations($days);

            $this->info("✓ Cleaned up {$result['expired_count']} expired allocations");
            $this->info("✓ Cleaned up {$result['history_count']} old history records");
            $this->info('Cleanup completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
