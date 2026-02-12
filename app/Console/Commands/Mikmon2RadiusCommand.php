<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;

class Mikmon2RadiusCommand extends Command
{
    protected $signature = 'mikmon2radius {router_ip} {user} {password} {port} {operator_id}';
    protected $description = 'Import existing Hotspot customers from MikroTik router to RADIUS';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $routerIp = $this->argument('router_ip');
        $user = $this->argument('user');
        $password = $this->argument('password');
        $port = $this->argument('port');
        $operatorId = $this->argument('operator_id');

        if ($this->mikrotikService->connect($routerIp, $user, $password, $port)) {
            $this->info("Connected to router: {$routerIp}");
            // Logic to import hotspot users will be added here
            $this->mikrotikService->disconnect();
        } else {
            $this->error("Failed to connect to router: {$routerIp}");
        }
    }
}
