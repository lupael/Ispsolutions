<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerImportRequest;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MikrotikDbSyncController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public static function sync(CustomerImportRequest $customer_import_request)
    {
        $router = $customer_import_request->router;
        $mikrotikService = app(MikrotikService::class);
        if ($mikrotikService->connect($router->nasname, $router->api_username, $router->api_password, $router->api_port)) {
            // Logic to import IP pools, PPP profiles, and PPP secrets
            // This will be implemented later
            $mikrotikService->disconnect();
        }
    }
}
