<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorAuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthentication
{
    protected TwoFactorAuthenticationService $twoFactorService;

    public function __construct(TwoFactorAuthenticationService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip 2FA check for 2FA-related routes
        if ($request->is('2fa/*')) {
            return $next($request);
        }

        // Check if user has 2FA enabled and hasn't verified in this session
        if ($this->twoFactorService->isEnabled($user) && !$request->session()->get('2fa_verified')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
