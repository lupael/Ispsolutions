<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikSyncAll extends Command
{
    protected $signature = 'mikrotik:sync-all {router : Router ID or name}';

    protected $description = 'Sync all data from MikroTik router (profiles, pools, secrets)';

    public function handle(MikrotikServiceInterface $mikrotikService): int
    {
        $routerIdentifier = $this->argument('router');

        $router = is_numeric($routerIdentifier)
            ? MikrotikRouter::find($routerIdentifier)
            : MikrotikRouter::where('name', $routerIdentifier)->first();

        if (! $router) {
            $this->error("Router not found: {$routerIdentifier}");

            return self::FAILURE;
        }

        $this->info("Syncing all data from router: {$router->name}");

        $this->info('Syncing profiles...');
        $profilesCount = $mikrotikService->syncProfiles($router->id);
        $this->line("  - {$profilesCount} profile(s) synced");

        $this->info('Syncing IP pools...');
        $poolsCount = $mikrotikService->syncIpPools($router->id);
        $this->line("  - {$poolsCount} pool(s) synced");

        $this->info('Syncing secrets...');
        $secretsCount = $mikrotikService->syncSecrets($router->id);
        $this->line("  - {$secretsCount} secret(s) synced");

        $totalSynced = $profilesCount + $poolsCount + $secretsCount;

        $this->newLine();
        $this->info("Sync completed! Total items synced: {$totalSynced}");

        return self::SUCCESS;
    }
}
