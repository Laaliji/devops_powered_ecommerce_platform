<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to allow unauthenticated access to auth routes.
 * This prevents Shield from blocking login/register pages.
 */
class AuthPageBypassShield
{
    public function handle(Request $request, Closure $next)
    {
        // Simply pass through - auth pages have canAccess() overrides
        // that handle permission checks
        return $next($request);
    }
}

