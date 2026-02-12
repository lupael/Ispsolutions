<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use App\Models\Operator;

class SyncOnlineCustomersWithApiCommand extends Command
{
    protected $signature = 'sync:online_customers {operator_id}';
    protected $description = 'Sync online customers with router API';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $operatorId = $this->argument('operator_id');
        $operator = Operator::find($operatorId);

        if (!$operator) {
            $this->error('Operator not found.');
            return;
        }

        foreach ($operator->routers as $router) {
            if ($this->mikrotikService->connect($router->nasname, $router->api_username, $router->api_password, $router->api_port)) {
                // Logic to sync online customers will be added here
                $this->info("Syncing online customers for router: {$router->shortname}");
                $this->mikrotikService->disconnect();
            } else {
                $this->error("Failed to connect to router: {$router->shortname}");
            }
        }
    }
}
