<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:health-check 
                            {--router= : Specific router ID to check}
                            {--verbose : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check connectivity and health of MikroTik routers';

    /**
     * Execute the console command.
     */
    public function handle(MikrotikServiceInterface $mikrotikService): int
    {
        $routerId = $this->option('router');
        $verbose = $this->option('verbose');

        $this->info('Checking MikroTik router health...');

        try {
            if ($routerId) {
                // Check specific router
                $router = MikrotikRouter::findOrFail($routerId);

                return $this->checkRouter($router, $mikrotikService, $verbose);
            } else {
                // Check all active routers
                $routers = MikrotikRouter::where('status', 'active')->get();

                if ($routers->isEmpty()) {
                    $this->warn('No active routers found');

                    return Command::SUCCESS;
                }

                $this->info("Checking {$routers->count()} router(s)...");
                $this->newLine();

                $healthy = 0;
                $unhealthy = 0;

                foreach ($routers as $router) {
                    $success = $mikrotikService->connectRouter($router->id);

                    if ($success) {
                        $this->info("✓ {$router->name} ({$router->ip_address}) - Healthy");
                        $healthy++;
                    } else {
                        $this->error("✗ {$router->name} ({$router->ip_address}) - Unreachable");
                        $unhealthy++;
                    }

                    if ($verbose) {
                        $this->line("  IP: {$router->ip_address}");
                        $this->line("  Port: {$router->api_port}");
                        $this->line("  Status: {$router->status}");
                        $this->newLine();
                    }
                }

                $this->newLine();
                $this->info('Health Check Summary:');
                $this->info("  Healthy: {$healthy}");
                if ($unhealthy > 0) {
                    $this->warn("  Unhealthy: {$unhealthy}");
                }

                return $unhealthy > 0 ? Command::FAILURE : Command::SUCCESS;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error('Router not found');

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function checkRouter(MikrotikRouter $router, MikrotikServiceInterface $mikrotikService, bool $verbose): int
    {
        $success = $mikrotikService->connectRouter($router->id);

        if ($success) {
            $this->info("✓ Router '{$router->name}' is healthy");

            if ($verbose) {
                $this->info('Details:');
                $this->line("  IP: {$router->ip_address}");
                $this->line("  Port: {$router->api_port}");
                $this->line("  Status: {$router->status}");
            }

            return Command::SUCCESS;
        } else {
            $this->error("✗ Router '{$router->name}' is unreachable");
            $this->warn('Check network connectivity and credentials');

            return Command::FAILURE;
        }
    }
}
