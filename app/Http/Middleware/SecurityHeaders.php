<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private array $headers = [
        'X-Content-Type-Options'    => 'nosniff',
        'X-Frame-Options'           => 'DENY',
        'X-XSS-Protection'          => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy'   => "default-src 'none'",
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=()',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
