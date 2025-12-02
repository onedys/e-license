<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleActivation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 60): Response
    {
        $key = 'activation:' . ($request->user()?->id ?: $request->ip());
        
        $executed = RateLimiter::attempt(
            $key,
            $maxAttempts,
            function() {
                // No action needed here
            },
            $decayMinutes * 60
        );

        if (!$executed) {
            return response()->view('errors.429', [], 429);
        }

        return $next($request);
    }
}