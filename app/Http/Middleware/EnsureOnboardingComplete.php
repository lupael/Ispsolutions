<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Panel\MinimumConfigurationController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Onboarding Middleware
 *
 * Redirects admin users to the onboarding page if they haven't completed
 * the minimum configuration requirements.
 */
class EnsureOnboardingComplete
{
    /**
     * Routes that should be accessible even without completing onboarding.
     */
    protected array $except = [
        'panel.admin.onboarding',
        'panel.admin.backup-settings.*',
        'panel.admin.billing-profiles.*',
        'panel.admin.network.routers.*',
        'panel.admin.packages.*',
        'panel.admin.customers.*',
        'panel.admin.operators.*',
        'panel.admin.profile.*',
        'logout',
        'language.switch',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for admin users
        if (! Auth::check() || Auth::user()->operator_level !== 20) {
            return $next($request);
        }

        // Skip check for excluded routes
        $currentRoute = $request->route()->getName();
        foreach ($this->except as $pattern) {
            if ($this->matchesPattern($currentRoute, $pattern)) {
                return $next($request);
            }
        }

        // Check if onboarding is complete
        $controller = new MinimumConfigurationController();
        if (! $controller->isOnboardingComplete(Auth::user())) {
            return redirect()->route('panel.admin.onboarding')
                ->with('warning', 'Please complete the onboarding process to access this feature.');
        }

        return $next($request);
    }

    /**
     * Check if route name matches pattern (supports wildcards).
     */
    protected function matchesPattern(string $routeName, string $pattern): bool
    {
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^'.$pattern.'$#', $routeName);
    }
}
