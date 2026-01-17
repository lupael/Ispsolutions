<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikImportPools extends Command
{
    protected $signature = 'mikrotik:import-pools {router : Router ID or name}';

    protected $description = 'Import IP pools from MikroTik router';

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

        $this->info("Importing IP pools from router: {$router->name}");

        $count = $mikrotikService->syncIpPools($router->id);

        if ($count > 0) {
            $this->info("Successfully imported {$count} IP pool(s)");

            return self::SUCCESS;
        }

        $this->error('Failed to import IP pools');

        return self::FAILURE;
    }
}
