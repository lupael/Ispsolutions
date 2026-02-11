<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Concerns\FindsAssociatedModel;
use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikImportSecrets extends Command
{
    use FindsAssociatedModel;

    protected $signature = 'mikrotik:import-secrets {router : Router ID or name}';

    protected $description = 'Import PPPoE secrets from MikroTik router';

    public function handle(MikrotikServiceInterface $mikrotikService): int
    {
        $routerIdentifier = $this->argument('router');

        try {
            /** @var MikrotikRouter $router */
            $router = $this->findModel(MikrotikRouter::class, $routerIdentifier, 'name');

            $this->info("Importing secrets from router: {$router->name}");

            $count = $mikrotikService->syncSecrets($router->id);

            if ($count > 0) {
                $this->info("Successfully imported {$count} secret(s)");

                return self::SUCCESS;
            }

            $this->warn('No secrets were imported. The router may not have any new secrets to sync.');

            return self::SUCCESS;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->error("Router not found: {$routerIdentifier}");

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Failed to import secrets: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
