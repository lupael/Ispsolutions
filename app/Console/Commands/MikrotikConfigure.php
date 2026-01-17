<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MikrotikServiceInterface;
use App\Models\MikrotikRouter;
use Illuminate\Console\Command;

class MikrotikConfigure extends Command
{
    protected $signature = 'mikrotik:configure {router : Router ID or name}
                            {--ppp : Configure PPPoE}
                            {--pools : Configure IP pools}
                            {--hotspot : Configure Hotspot}
                            {--pppoe : Configure PPPoE server}
                            {--firewall : Configure Firewall}
                            {--queue : Configure Queue}
                            {--radius : Configure RADIUS}';

    protected $description = 'One-click configuration for MikroTik router';

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

        $config = [];

        if ($this->option('ppp')) {
            $config['ppp'] = true;
        }

        if ($this->option('pools')) {
            $config['pools'] = true;
        }

        if ($this->option('hotspot')) {
            $config['hotspot'] = true;
        }

        if ($this->option('pppoe')) {
            $config['pppoe'] = true;
        }

        if ($this->option('firewall')) {
            $config['firewall'] = true;
        }

        if ($this->option('queue')) {
            $config['queue'] = true;
        }

        if ($this->option('radius')) {
            $config['radius'] = true;
        }

        if (empty($config)) {
            $this->error('No configuration options specified. Use --help to see available options.');

            return self::FAILURE;
        }

        $this->info("Configuring router: {$router->name}");

        $success = $mikrotikService->configureRouter($router->id, $config);

        if ($success) {
            $this->info('Router configured successfully');

            return self::SUCCESS;
        }

        $this->error('Failed to configure router');

        return self::FAILURE;
    }
}
