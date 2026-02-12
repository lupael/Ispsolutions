<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Nas;
use Illuminate\Http\Request;
use App\Services\MikrotikService;

class QueryInRouterController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public static function getOnlineStatus(Customer $customer)
    {
        $router = $customer->router;
        $mikrotikService = app(MikrotikService::class);
        if ($mikrotikService->connect($router->nasname, $router->api_username, $router->api_password, $router->api_port)) {
            $activeUsers = [];
            if ($customer->connection_type === 'pppoe') {
                $activeUsers = $mikrotikService->getPppActive();
            } elseif ($customer->connection_type === 'hotspot') {
                $activeUsers = $mikrotikService->getHotspotActive();
            }
            $mikrotikService->disconnect();

            foreach ($activeUsers as $user) {
                if ($user['name'] === $customer->username) {
                    return count($activeUsers);
                }
            }
        }
        return false;
    }
}
