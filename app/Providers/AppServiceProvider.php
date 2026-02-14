<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind SNMP client interface to the PHP implementation
        $this->app->singleton(\App\Contracts\SnmpClientInterface::class, function ($app) {
            return new \App\Services\PhpSnmpClient();
        });

        // CRITICAL: Bind OltServiceInterface to OltService implementation
        // This fixes the TypeError in your Artisan commands
        $this->app->bind(
            \App\Contracts\OltServiceInterface::class,
            \App\Services\OltService::class
        );

        // Singleton for OltSnmpService to handle V-SOL OIDs
        $this->app->singleton(\App\Services\OltSnmpService::class, function ($app) {
            return new \App\Services\OltSnmpService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\MikrotikRouter::observe(\App\Observers\MikrotikRouterObserver::class);

        // Configure rate limiters
        \Illuminate\Support\Facades\RateLimiter::for('distributor-api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ImportPppCustomersRequested::class,
            \App\Listeners\ImportPppCustomersListener::class
        );

        // Register policies
        Gate::policy(\App\Models\User::class, \App\Policies\CustomerPolicy::class);
        Gate::policy(\App\Models\MikrotikRouter::class, \App\Policies\MikrotikRouterPolicy::class);
        Gate::policy(\App\Models\MikrotikPppoeUser::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\Nas::class, \App\Policies\NasPolicy::class);
        Gate::policy(\App\Models\Olt::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\IpPool::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\MikrotikIpPool::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\Package::class, \App\Policies\PackagePolicy::class);
        Gate::policy(\App\Models\ServicePackage::class, \App\Policies\PackagePolicy::class);
        Gate::policy(\App\Models\Ticket::class, \App\Policies\TicketPolicy::class);
        Gate::policy(\App\Models\Lead::class, \App\Policies\LeadPolicy::class);
        Gate::policy(\App\Models\Invoice::class, \App\Policies\InvoicePolicy::class);
        Gate::policy(\App\Models\NetworkUserSession::class, \App\Policies\NetworkUserSessionPolicy::class);
        Gate::policy(\App\Models\RechargeCard::class, \App\Policies\RechargeCardPolicy::class);

        // Define authorization gates for new features
        Gate::define('view-audit-logs', function ($user) {
            return $user->operator_level <= 30; 
        });

        Gate::define('manage-api-keys', function ($user) {
            return $user->operator_level <= 20; 
        });

        Gate::define('view-analytics', function ($user) {
            return $user->operator_level <= 40; 
        });

        Gate::define('manage-network-devices', function ($user) {
            return $this->canManageResource($user, 'network.manage');
        });

        // Register CLI commands for SNMP diagnostics
        if (class_exists(\App\Console\Commands\OltSnmpTest::class)) {
            $this->commands([
                \App\Console\Commands\OltSnmpTest::class,
            ]);
        }

        // Add the sync command to the console
        if (class_exists(\App\Console\Commands\OltSyncOnus::class)) {
            $this->commands([
                \App\Console\Commands\OltSyncOnus::class,
            ]);
        }

        Gate::define('manage-packages', function ($user) {
            return $this->canManageResource($user, 'packages.manage');
        });

        Gate::define('set-suboperator-pricing', function ($user) {
            return $user->isOperatorRole()
                || $user->isAdmin()
                || $user->isDeveloper()
                || $user->isSuperAdmin();
        });

        Gate::define('manage-customers', function ($user) {
            return $user->operator_level <= 20;
        });
    }

    /**
     * Helper method to check if user can manage a resource.
     */
    private function canManageResource($user, string $permission): bool
    {
        return $user->isAdmin()
            || $user->isDeveloper()
            || $user->isSuperAdmin()
            || $user->hasSpecialPermission($permission);
    }
}