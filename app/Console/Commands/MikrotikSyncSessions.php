<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\FindsAssociatedModel;
use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use App\Models\RadiusSession;
use Illuminate\Console\Command;

class MikrotikSyncSessions extends Command
{
    use FindsAssociatedModel;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync-sessions
                            {--router= : Specific router ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync active sessions from MikroTik routers to local database';

    /**
     * Execute the console command.
     */
    public function handle(MikrotikServiceInterface $mikrotikService): int
    {
        $routerIdentifier = $this->option('router');

        $this->info('Syncing MikroTik sessions...');

        try {
            if ($routerIdentifier) {
                // Sync specific router
                /** @var MikrotikRouter $router */
                $router = $this->findModel(MikrotikRouter::class, $routerIdentifier);

                return $this->syncRouterSessions($router, $mikrotikService);
            } else {
                // Sync all active routers
                $routers = MikrotikRouter::where('status', 'active')->get();

                if ($routers->isEmpty()) {
                    $this->warn('No active routers found');

                    return Command::SUCCESS;
                }

                $totalSynced = 0;
                $failedRouters = 0;

                foreach ($routers as $router) {
                    $result = $this->syncRouterSessions($router, $mikrotikService, false);
                    if ($result['success']) {
                        $totalSynced += $result['count'];
                    } else {
                        $failedRouters++;
                    }
                }

                $this->newLine();
                $this->info('Sync Summary:');
                $this->info("  Total sessions synced: {$totalSynced}");
                if ($failedRouters > 0) {
                    $this->warn("  Failed routers: {$failedRouters}");
                }

                return $failedRouters > 0 ? Command::FAILURE : Command::SUCCESS;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Router not found: {$routerIdentifier}");

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Session sync failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @return array{success: bool, count: int}
     */
    private function syncRouterSessions(MikrotikRouter $router, MikrotikServiceInterface $mikrotikService, bool $isSingle = true): array
    {
        try {
            $sessions = $mikrotikService->getActiveSessions($router->id);

            if (empty($sessions)) {
                $message = "No active sessions found on router '{$router->name}'";
                $isSingle ? $this->warn($message) : $this->line("  - {$message}");

                return ['success' => true, 'count' => 0];
            }

            $syncedCount = 0;
            foreach ($sessions as $session) {
                // Normalize session data structure
                $sessionId = $session['.id'] ?? $session['name'] ?? null;
                $username = $session['name'] ?? $session['user'] ?? null;
                $ipAddress = $session['address'] ?? $session['framed-ip-address'] ?? null;
                $uptime = $session['uptime'] ?? '0';
                $bytesIn = $session['bytes-in'] ?? $session['input-octets'] ?? 0;
                $bytesOut = $session['bytes-out'] ?? $session['output-octets'] ?? 0;

                if (! $sessionId || ! $username) {
                    continue; // Skip invalid session data
                }

                RadiusSession::updateOrCreate(
                    ['session_id' => $sessionId, 'nas_ip_address' => $router->ip_address],
                    [
                        'username' => $username,
                        'framed_ip_address' => $ipAddress,
                        'start_time' => now()->subSeconds((int) $uptime),
                        'input_octets' => (int) $bytesIn,
                        'output_octets' => (int) $bytesOut,
                        'status' => 'active',
                    ]
                );
                $syncedCount++;
            }

            $this->info("✓ Synced {$syncedCount} sessions from '{$router->name}'");

            return ['success' => true, 'count' => $syncedCount];
        } catch (\Exception $e) {
            $this->error("✗ Error syncing '{$router->name}': " . $e->getMessage());

            return ['success' => false, 'count' => 0];
        }
    }
}
