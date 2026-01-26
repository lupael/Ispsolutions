<?php

namespace App\Console\Commands;

use App\Models\MikrotikRouter;
use App\Services\RouterBackupService;
use Illuminate\Console\Command;

class RouterMirrorUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:mirror-users {router : The router ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mirror (sync) all active users from database to router';

    protected RouterBackupService $backupService;

    public function __construct(RouterBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routerId = $this->argument('router');

        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            $this->error("Router with ID {$routerId} not found");
            return 1;
        }

        $this->info("Mirroring users to router: {$router->name}");
        $this->info("This will sync all active PPPoE users from the database to the router");
        $this->newLine();

        $result = $this->backupService->mirrorCustomersToRouter($router);
        
        if (!$result['success']) {
            $this->error("✗ Failed to mirror users");
            $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
            return 1;
        }

        $this->info("✓ Users mirrored successfully");
        $this->newLine();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', $result['total']],
                ['Synced Successfully', $result['synced']],
                ['Failed', $result['failed']],
            ]
        );

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->warn("Errors encountered:");
            foreach ($result['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        return 0;
    }
}

