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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        // Configure rate limiters
        \Illuminate\Support\Facades\RateLimiter::for('distributor-api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ImportPppCustomersRequested::class,
            \App\Listeners\ImportPppCustomersListener::class
        );

        // Note: UserCreated and PasswordChanged events need to be created
        // and fired from the appropriate places (CustomerController, AuthController, etc.)
        // Uncomment these when those events are implemented:
        // \Illuminate\Support\Facades\Event::listen(
        //     \App\Events\UserCreated::class,
        //     \App\Listeners\ProvisionUserAfterCreation::class
        // );
        // \Illuminate\Support\Facades\Event::listen(
        //     \App\Events\PasswordChanged::class,
        //     \App\Listeners\UpdateRouterOnPasswordChange::class
        // );

        // Register policies
        Gate::policy(\App\Models\MikrotikRouter::class, \App\Policies\MikrotikRouterPolicy::class);
        Gate::policy(\App\Models\MikrotikPppoeUser::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\Nas::class, \App\Policies\NasPolicy::class);
        Gate::policy(\App\Models\Olt::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\IpPool::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\MikrotikIpPool::class, \App\Policies\NetworkDevicePolicy::class);
        Gate::policy(\App\Models\Package::class, \App\Policies\PackagePolicy::class);
        Gate::policy(\App\Models\ServicePackage::class, \App\Policies\PackagePolicy::class);
        Gate::policy(\App\Models\Ticket::class, \App\Policies\TicketPolicy::class);

        // Define authorization gates for new features
        Gate::define('view-audit-logs', function ($user) {
            // Only allow admins, super admins, developers, and operators to view audit logs
            return $user->operator_level <= 30; // Developer, Super Admin, Admin, Operator
        });

        Gate::define('manage-api-keys', function ($user) {
            // Only allow admins and higher to manage API keys
            return $user->operator_level <= 20; // Developer, Super Admin, Admin
        });

        Gate::define('view-analytics', function ($user) {
            // Only allow operators and higher to view analytics
            return $user->operator_level <= 40; // Developer, Super Admin, Admin, Operator, Sub-operator
        });

        // Define gates for network device management
        Gate::define('manage-network-devices', function ($user) {
            return $this->canManageResource($user, 'network.manage');
        });

        Gate::define('manage-packages', function ($user) {
            return $this->canManageResource($user, 'packages.manage');
        });

        Gate::define('set-suboperator-pricing', function ($user) {
            // Operators can set prices for their Sub-Operators
            return $user->isOperatorRole()
                || $user->isAdmin()
                || $user->isDeveloper()
                || $user->isSuperAdmin();
        });
    }

    /**
     * Helper method to check if user can manage a resource.
     * Only Admin can manage by default. Staff/Manager can manage if they have explicit permission.
     */
    private function canManageResource($user, string $permission): bool
    {
        return $user->isAdmin()
            || $user->isDeveloper()
            || $user->isSuperAdmin()
            || $user->hasSpecialPermission($permission);
    }
}
