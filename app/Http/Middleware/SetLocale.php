<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set Application Locale Middleware
 * Task 6.2: Add language switcher to UI
 * 
 * Sets the application locale based on:
 * 1. User's stored language preference (if authenticated)
 * 2. Session locale (if set)
 * 3. Default application locale (fallback)
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getPreferredLocale();
        
        App::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Get the preferred locale for the current user
     */
    private function getPreferredLocale(): string
    {
        // 1. Check if user is authenticated and has a language preference
        $user = Auth::user();
        if ($user && isset($user->language) && $this->isValidLocale($user->language)) {
            return $user->language;
        }

        // 2. Check session for locale
        if (Session::has('locale') && $this->isValidLocale(Session::get('locale'))) {
            return Session::get('locale');
        }

        // 3. Fall back to default locale
        return config('app.locale', 'en');
    }

    /**
     * Check if a locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        $availableLocales = ['en', 'bn'];
        return in_array($locale, $availableLocales);
    }
}
