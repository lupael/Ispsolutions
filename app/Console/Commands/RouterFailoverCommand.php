<?php

namespace App\Console\Commands;

use App\Models\MikrotikRouter;
use App\Services\RouterRadiusFailoverService;
use Illuminate\Console\Command;

class RouterFailoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:failover {router : The router ID} {--mode= : Switch mode (radius|router)} {--configure : Configure Netwatch failover}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage RADIUS failover for a router';

    protected RouterRadiusFailoverService $failoverService;

    public function __construct(RouterRadiusFailoverService $failoverService)
    {
        parent::__construct();
        $this->failoverService = $failoverService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routerId = $this->argument('router');
        $mode = $this->option('mode');
        $configure = $this->option('configure');

        $router = MikrotikRouter::find($routerId);
        
        if (!$router) {
            $this->error("Router with ID {$routerId} not found");
            return 1;
        }

        $this->info("Managing failover for router: {$router->name}");

        if ($configure) {
            return $this->configureFailover($router);
        }

        if ($mode) {
            return $this->switchMode($router, $mode);
        }

        // Default: Show status
        return $this->showStatus($router);
    }

    protected function configureFailover(MikrotikRouter $router): int
    {
        $this->info("Configuring Netwatch failover...");
        
        $result = $this->failoverService->configureFailover($router);
        
        if ($result) {
            $this->info("✓ Failover configured successfully");
            return 0;
        }

        $this->error("✗ Failed to configure failover");
        return 1;
    }

    protected function switchMode(MikrotikRouter $router, string $mode): int
    {
        $this->info("Switching to {$mode} mode...");
        
        $result = match ($mode) {
            'radius' => $this->failoverService->switchToRadiusMode($router),
            'router' => $this->failoverService->switchToRouterMode($router),
            default => false,
        };
        
        if ($result) {
            $this->info("✓ Switched to {$mode} mode successfully");
            return 0;
        }

        $this->error("✗ Failed to switch mode");
        return 1;
    }

    protected function showStatus(MikrotikRouter $router): int
    {
        $status = $this->failoverService->getRadiusStatus($router);
        
        if (!$status['connected']) {
            $this->error("✗ Failed to connect to router");
            return 1;
        }

        $this->info("RADIUS Failover Status for {$router->name}:");
        $this->newLine();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['RADIUS Configured', $status['radius_configured'] ? '✓ Yes' : '✗ No'],
                ['RADIUS Enabled', $status['radius_enabled'] ? '✓ Yes' : '✗ No'],
                ['Accounting Enabled', $status['accounting_enabled'] ? '✓ Yes' : '✗ No'],
                ['Primary Auth Mode', $status['primary_auth']],
                ['Netwatch Configured', $status['netwatch_configured'] ? '✓ Yes' : '✗ No'],
                ['Netwatch Status', $status['netwatch_status'] ?? 'N/A'],
                ['RADIUS Server', $status['radius_server']],
            ]
        );

        return 0;
    }
}

