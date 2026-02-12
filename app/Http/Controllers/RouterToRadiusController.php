<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Nas;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class RouterToRadiusController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public static function transfer(Nas $router, Customer $customer)
    {
        $mikrotikService = app(MikrotikService::class);
        if ($mikrotikService->connect($router->nasname, $router->api_username, $router->api_password, $router->api_port)) {
            // Logic to disable customer in router and disconnect active sessions
            // This will be implemented later
            $mikrotikService->disconnect();
        }
    }
}
