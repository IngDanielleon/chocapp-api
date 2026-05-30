<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByUser
{
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $key = $limiter . ':' . ($request->user()?->id ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts($limiter))) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Intenta más tarde.',
                'errors'  => null,
                'code'    => 429,
            ], 429);
        }

        RateLimiter::hit($key, $this->decaySeconds($limiter));

        return $next($request);
    }

    private function maxAttempts(string $limiter): int
    {
        return match($limiter) {
            'auth'  => 5,
            'heavy' => 10,
            default => 60,
        };
    }

    private function decaySeconds(string $limiter): int
    {
        return 60;
    }
}
