<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
            
            $response = $next($request);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            
            $contentType = $response->headers->get('Content-Type');
            if (!$contentType || !str_contains($contentType, 'application/json')) {
                $response->headers->set('Content-Type', 'application/json');
            }
            
            return $response;
        }
        
        return $next($request);
    }
}