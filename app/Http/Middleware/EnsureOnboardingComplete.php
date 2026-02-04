<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Onboarding Middleware
 *
 * Redirects ISP users to the onboarding page if they haven't completed
 * the minimum configuration requirements.
 */
class EnsureOnboardingComplete
{
    /**
     * Routes that should be accessible even without completing onboarding.
     */
    protected array $except = [
        'panel.isp.onboarding',
        'panel.isp.backup-settings.*',
        'panel.isp.billing-profiles.*',
        'panel.isp.network.routers.*',
        'panel.isp.packages.*',
        'panel.isp.customers.*',
        'panel.isp.operators.*',
        'panel.isp.profile.*',
        'logout',
        'language.switch',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for ISP users
        if (! Auth::check() || Auth::user()->operator_level !== User::OPERATOR_LEVEL_ISP) {
            return $next($request);
        }

        // Get the current route name
        $currentRoute = $request->route()?->getName();
        if (! $currentRoute) {
            return $next($request);
        }

        // Skip check for excluded routes
        foreach ($this->except as $pattern) {
            if ($this->matchesPattern($currentRoute, $pattern)) {
                return $next($request);
            }
        }

        // Check if onboarding is complete
        if (! $this->isOnboardingComplete(Auth::user())) {
            return redirect()->route('panel.isp.onboarding')
                ->with('warning', 'Please complete the onboarding process to access this feature.');
        }

        return $next($request);
    }

    /**
     * Check if onboarding is complete for the given user.
     */
    protected function isOnboardingComplete(User $user): bool
    {
        // This duplicates logic from MinimumConfigurationController
        // to avoid tight coupling, but maintains the same checks

        // Check billing profile exists
        if (! \App\Models\BillingProfile::where('tenant_id', $user->tenant_id)->exists()) {
            return false;
        }

        // Check router exists
        if (! \App\Models\Nas::where('tenant_id', $user->tenant_id)->exists()) {
            return false;
        }

        // Check backup settings configured
        if (! \App\Models\BackupSetting::where('operator_id', $user->id)->exists()) {
            return false;
        }

        // Check profile completed
        if (empty($user->company_in_native_lang)) {
            return false;
        }

        return true;
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
