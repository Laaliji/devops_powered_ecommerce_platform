<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures only super admins can access the admin panel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();

            // Only admins can access admin panel
            if (!$user->hasRole('admin')) {
                abort(403, 'Access denied. Admin privileges required.');
            }
        }

        return $next($request);
    }
}
