<?php

namespace App\Console\Commands;

use App\Models\MikrotikRouter;
use App\Services\RouterBackupService;
use Illuminate\Console\Command;

class RouterBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:backup {router : The router ID} {--type=manual : Backup type (manual|scheduled)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of router configuration';

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
        $type = $this->option('type');

        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            $this->error("Router with ID {$routerId} not found");
            return 1;
        }

        $this->info("Creating {$type} backup for router: {$router->name}");

        $backup = match ($type) {
            'scheduled' => $this->backupService->createScheduledBackup($router),
            default => $this->backupService->createManualBackup(
                $router,
                'Manual backup - ' . now()->format('Y-m-d H:i:s'),
                'CLI backup command'
            ),
        };

        if ($backup) {
            $this->info("✓ Backup created successfully");
            $this->table(
                ['ID', 'Name', 'Type', 'Created At'],
                [[$backup->id, $backup->backup_name, $backup->backup_type, $backup->created_at]]
            );
            return 0;
        }

        $this->error("✗ Failed to create backup");
        return 1;
    }
}

