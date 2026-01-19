<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy
        // Generate a nonce for inline scripts and styles
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        
        // Note: 'unsafe-eval' is required for Alpine.js to evaluate expressions in attributes like x-data, x-show, @click, etc.
        // Alpine.js uses Function() constructor which requires eval. While this slightly weakens CSP,
        // it's necessary for Alpine.js to work. Alternative would be to use Alpine's CSP build which has limitations.
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-eval' 'nonce-{$nonce}' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.tailwindcss.com static.cloudflareinsights.com; " .
            "style-src 'self' 'unsafe-inline' 'nonce-{$nonce}' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com fonts.bunny.net; " .
            "font-src 'self' fonts.gstatic.com fonts.bunny.net cdnjs.cloudflare.com cdn.jsdelivr.net; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self'; " .
            "frame-ancestors 'self';"
        );

        // Permissions Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=()'
        );

        // Strict Transport Security (HTTPS only)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
