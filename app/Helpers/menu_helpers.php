<?php

if (! function_exists('isMenuActive')) {
    /**
     * Check if a menu route is currently active.
     *
     * @param  string|array  $routes
     */
    function isMenuActive($routes): bool
    {
        $routes = is_array($routes) ? $routes : [$routes];

        foreach ($routes as $route) {
            if (request()->routeIs($route) || request()->routeIs($route . '.*')) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('isMenuDisabled')) {
    /**
     * Check if a menu is disabled for the authenticated user.
     */
    function isMenuDisabled(string $menuKey): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isMenuDisabled($menuKey);
    }
}

if (! function_exists('canAccessMenu')) {
    /**
     * Check if user can access a menu item.
     * 
     * This function checks if the authenticated user can access a given menu item
     * based on disabled menus and permissions. Menus without explicit permissions
     * are accessible to all authenticated users by default.
     */
    function canAccessMenu(array $menuItem): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Check if menu is disabled
        if (isset($menuItem['key']) && $user->isMenuDisabled($menuItem['key'])) {
            return false;
        }

        // Check permission if specified
        if (isset($menuItem['permission'])) {
            return $user->hasPermission($menuItem['permission']);
        }

        // Menus without explicit permissions are accessible to all authenticated users
        return true;
    }
}

if (! function_exists('getSidebarMenu')) {
    /**
     * Get sidebar menu for the authenticated user.
     */
    function getSidebarMenu(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $operatorType = $user->operator_type ?? 'customer';
        $allMenus = config("sidebars.{$operatorType}", []);

        // Filter menus based on permissions and disabled status
        return array_filter($allMenus, function ($menu) {
            return canAccessMenu($menu);
        });
    }
}

if (! function_exists('formatCurrency')) {
    /**
     * Format amount as currency.
     */
    function formatCurrency(float $amount, string $currency = 'BDT'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (! function_exists('getCurrentTenant')) {
    /**
     * Get the current tenant.
     */
    function getCurrentTenant(): ?\App\Models\Tenant
    {
        $tenancyService = app(\App\Services\TenancyService::class);

        return $tenancyService->getCurrentTenant();
    }
}

if (! function_exists('getCurrentTenantId')) {
    /**
     * Get the current tenant ID.
     */
    function getCurrentTenantId(): ?int
    {
        $tenancyService = app(\App\Services\TenancyService::class);

        return $tenancyService->getCurrentTenantId();
    }
}
