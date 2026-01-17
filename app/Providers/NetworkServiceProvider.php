<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\IpamServiceInterface;
use App\Contracts\MikrotikServiceInterface;
use App\Contracts\MonitoringServiceInterface;
use App\Contracts\OltServiceInterface;
use App\Contracts\RadiusServiceInterface;
use App\Services\IpamService;
use App\Services\MikrotikService;
use App\Services\MonitoringService;
use App\Services\OltService;
use App\Services\RadiusService;
use Illuminate\Support\ServiceProvider;

class NetworkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind IPAM Service
        $this->app->singleton(IpamServiceInterface::class, IpamService::class);

        // Bind RADIUS Service
        $this->app->singleton(RadiusServiceInterface::class, RadiusService::class);

        // Bind MikroTik Service (scoped per request to avoid sharing stateful instances)
        $this->app->scoped(MikrotikServiceInterface::class, MikrotikService::class);

        // Bind OLT Service (scoped per request to avoid sharing SSH connections)
        $this->app->scoped(OltServiceInterface::class, OltService::class);

        // Bind Monitoring Service
        $this->app->singleton(MonitoringServiceInterface::class, MonitoringService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
