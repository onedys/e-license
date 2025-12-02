<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }
        
        return $request->expectsJson() ? null : route('login');
    }
    
    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*')) {
            throw new AuthenticationException(
                'Unauthenticated.', $guards, $request->is('api/*') ? $this->redirectApi($request) : $this->redirectTo($request)
            );
        }
        
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
    
    protected function redirectApi(Request $request): ?string
    {
        return null;
    }
}