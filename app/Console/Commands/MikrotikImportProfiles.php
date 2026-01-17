<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikImportProfiles extends Command
{
    protected $signature = 'mikrotik:import-profiles {router : Router ID or name}';

    protected $description = 'Import PPPoE profiles from MikroTik router';

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

        $this->info("Importing profiles from router: {$router->name}");

        $count = $mikrotikService->syncProfiles($router->id);

        if ($count > 0) {
            $this->info("Successfully imported {$count} profile(s)");

            return self::SUCCESS;
        }

        $this->error('Failed to import profiles');

        return self::FAILURE;
    }
}
