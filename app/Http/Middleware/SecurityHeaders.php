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
        // Use nonce-based CSP for better security (avoid unsafe-inline/unsafe-eval)
        // TODO: Implement nonce generation and injection for inline scripts
        // For now, use a more restrictive policy that still allows necessary CDNs
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com; " .
            "style-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com; " .
            "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; " .
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
