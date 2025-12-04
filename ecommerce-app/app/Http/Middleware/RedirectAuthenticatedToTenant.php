<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated users with a tenant to their tenant subdomain
 * when accessing the base /tenant path
 */
class RedirectAuthenticatedToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle /tenant path
        if ($request->path() === 'tenant') {
            // If user is authenticated and has a current tenant
            if (Auth::check() && Auth::user()->current_tenant_id) {
                $tenant = Auth::user()->currentTenant;
                if ($tenant) {
                    // Redirect to tenant subdomain
                    return redirect()->away($tenant->url . '/tenant');
                }
            }
        }

        return $next($request);
    }
}
