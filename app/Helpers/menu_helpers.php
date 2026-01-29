<?php

if (! function_exists('isMenuActive')) {
    /**
     * Check if a menu route is currently active.
     *
     * @param string|array $routes
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

        // Backward compatibility: map group_admin to admin
        if ($operatorType === 'group_admin') {
            $operatorType = 'admin';
        }

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

if (! function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for inline scripts and styles.
     */
    function csp_nonce(): string
    {
        return request()->attributes->get('csp_nonce', '');
    }
}

if (! function_exists('ordinal')) {
    /**
     * Format a number with ordinal suffix (1 -> "1st", 21 -> "21st")
     * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #3 (Date Formatting Enhancement)
     */
    function ordinal(int $number): string
    {
        return \App\Helpers\DateHelper::ordinal($number);
    }
}

if (! function_exists('dayWithOrdinal')) {
    /**
     * Format a day of the month with ordinal suffix (21 -> "21st day")
     */
    function dayWithOrdinal(int $day): string
    {
        return \App\Helpers\DateHelper::dayWithOrdinal($day);
    }
}

if (! function_exists('billingDayText')) {
    /**
     * Format a billing day with full text (21 -> "21st day of each month")
     */
    function billingDayText(int $day): string
    {
        return \App\Helpers\DateHelper::billingDayText($day);
    }
}

if (! function_exists('relativeTime')) {
    /**
     * Get relative time until a date ("Expires in 5 days", "Expired 3 days ago")
     */
    function relativeTime(\Carbon\Carbon|string $date, bool $short = false): string
    {
        return \App\Helpers\DateHelper::relativeTime($date, $short);
    }
}

if (! function_exists('expiryText')) {
    /**
     * Get expiry status text with relative time ("Expires in 5 days")
     * 
     * @param \Carbon\Carbon|string|null $expiryDate
     */
    function expiryText(\Carbon\Carbon|string|null $expiryDate, bool $short = false): string
    {
        return \App\Helpers\DateHelper::expiryText($expiryDate, $short);
    }
}

if (! function_exists('gracePeriodText')) {
    /**
     * Get grace period display text ("5 days grace period")
     */
    function gracePeriodText(int $days): string
    {
        return \App\Helpers\DateHelper::gracePeriodText($days);
    }
}

if (! function_exists('durationText')) {
    /**
     * Format duration in seconds to human-readable format (3h 25m 10s)
     */
    function durationText(int $seconds, bool $short = false): string
    {
        return \App\Helpers\DateHelper::duration($seconds, $short);
    }
}

