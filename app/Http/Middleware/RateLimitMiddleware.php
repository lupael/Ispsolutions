<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limitType = 'api'): Response
    {
        $config = config("rate-limiting.{$limitType}", config('rate-limiting.global'));

        if (!$config['enabled']) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request, $limitType);
        $maxAttempts = $config['max_attempts'];
        $decayMinutes = $config['decay_minutes'];

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildRateLimitResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key, $maxAttempts);
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request, string $limitType): string
    {
        $user = $request->user();
        $identifier = $user ? $user->id : $request->ip();

        return sprintf('rate_limit:%s:%s:%s', 
            $limitType,
            $identifier,
            $request->path()
        );
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildRateLimitResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        return response()->view('errors.429', [
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders(Response $response, string $key, int $maxAttempts): Response
    {
        $remaining = $this->limiter->remaining($key, $maxAttempts);
        $retryAfter = $this->limiter->availableIn($key);

        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);

        if ($remaining === 0) {
            $response->headers->add([
                'Retry-After' => $retryAfter,
            ]);
        }

        return $response;
    }
}
