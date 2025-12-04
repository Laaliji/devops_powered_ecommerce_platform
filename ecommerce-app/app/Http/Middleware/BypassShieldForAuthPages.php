<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bypasses Filament Shield authorization checks for authentication routes
 * This allows unauthenticated users to access /tenant/login and /tenant/register
 */
class BypassShieldForAuthPages
{
    public function handle(Request $request, Closure $next): Response
    {
        // List of auth routes that should bypass Shield
        $authRoutes = [
            '/tenant/login',
            '/tenant/register',
            '/tenant/password-reset',
            '/tenant/forgot-password',
            '/tenant/email-verification',
        ];

        $currentPath = '/' . ltrim($request->path(), '/');
        
        // Check if current path matches any auth route
        foreach ($authRoutes as $route) {
            if (str_starts_with($currentPath, $route)) {
                // Bypass Shield by setting a request flag
                // This will be checked by Shield to skip authorization
                $request->attributes->set('skip_shield_authorization', true);
                break;
            }
        }

        return $next($request);
    }
}
