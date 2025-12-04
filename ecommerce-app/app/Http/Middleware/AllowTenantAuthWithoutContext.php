<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowTenantAuthWithoutContext
{
    /**
     * Handle an incoming request.
     *
     * This middleware allows /tenant/login and /tenant/register to be accessed
     * without a subdomain tenant context by skipping the tenant domain requirement.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a login, register, or password reset request
        $path = $request->getPathInfo();
        $isAuthPath = str_contains($path, '/tenant/login') || 
                      str_contains($path, '/tenant/register') || 
                      str_contains($path, '/tenant/password') ||
                      str_contains($path, '/tenant/email');

        // If it's an auth path and there's no subdomain, allow it to proceed
        // Filament's tenant domain check will be skipped for these paths
        if ($isAuthPath && !$this->hasSubdomain($request)) {
            // Store a flag that this is a base-domain auth request
            $request->attributes->set('is_base_domain_auth', true);
        }

        return $next($request);
    }

    /**
     * Check if the request has a subdomain.
     */
    private function hasSubdomain(Request $request): bool
    {
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'localhost');

        return $host !== $baseDomain && str_ends_with($host, '.' . $baseDomain);
    }
}
