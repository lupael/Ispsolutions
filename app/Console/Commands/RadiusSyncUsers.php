<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\RadiusServiceInterface;
use App\Models\NetworkUser;
use Illuminate\Console\Command;

class RadiusSyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radius:sync-users {--status=active : Only sync users with specific status} {--force : Force sync without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all network users to the RADIUS database';

    /**
     * Execute the console command.
     */
    public function handle(RadiusServiceInterface $radiusService): int
    {
        $status = $this->option('status');
        $force = $this->option('force');

        $query = NetworkUser::query();

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->get();
        $total = $users->count();

        $this->info("Found {$total} users to sync");

        if (! $force && ! $this->confirm('Do you want to continue?', true)) {
            $this->info('Sync cancelled.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $success = $radiusService->syncUser($user, null);
                if ($success) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed to sync user '{$user->username}': " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Synced {$synced} users successfully");
        if ($failed > 0) {
            $this->warn("✗ Failed to sync {$failed} users");
        }

        return Command::SUCCESS;
    }
}
