<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use EvilFreelancer\RouterOS\API;
use Illuminate\Support\Facades\Log;

class MikrotikApiService
{
    private ?API $api = null;

    public function __construct(private readonly MikrotikRouter $router)
    {
    }

    public function connect(): bool
    {
        if ($this->api && $this->api->isConnected()) {
            return true;
        }

        try {
            $this->api = new API([
                'host' => $this->router->ip_address,
                'user' => $this->router->username,
                'pass' => $this->router->password,
                'port' => $this->router->api_port,
                'timeout' => (int) config('services.mikrotik.timeout', 30),
                'debug' => config('app.debug'),
            ]);
            return $this->api->isConnected();
        } catch (\Exception $e) {
            Log::error('MikroTik API connection failed', [
                'router_id' => $this->router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function disconnect(): void
    {
        $this->api?->disconnect();
    }

    public function getApi(): ?API
    {
        return $this->api;
    }
}
