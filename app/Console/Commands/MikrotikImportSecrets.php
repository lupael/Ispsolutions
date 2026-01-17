<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikImportSecrets extends Command
{
    protected $signature = 'mikrotik:import-secrets {router : Router ID or name}';

    protected $description = 'Import PPPoE secrets from MikroTik router';

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

        $this->info("Importing secrets from router: {$router->name}");

        $count = $mikrotikService->syncSecrets($router->id);

        if ($count > 0) {
            $this->info("Successfully imported {$count} secret(s)");

            return self::SUCCESS;
        }

        $this->error('Failed to import secrets');

        return self::FAILURE;
    }
}
