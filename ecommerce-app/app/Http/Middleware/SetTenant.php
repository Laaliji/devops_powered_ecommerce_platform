<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    /**
     * Handle an incoming request.
     *
     * Extracts tenant from subdomain and sets up the tenant context.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is an auth page (login/register) - allow without tenant context
        $path = $request->getPathInfo();
        $isAuthPath = str_contains($path, '/tenant/login') || 
                      str_contains($path, '/tenant/register') || 
                      str_contains($path, '/tenant/password') ||
                      str_contains($path, '/tenant/email');
        
        // Only try to set tenant context if we're on a subdomain or user is authenticated
        $tenant = null;
        
        // Check if we're on a subdomain
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'localhost');
        
        if ($host !== $baseDomain && str_ends_with($host, '.' . $baseDomain)) {
            // Extract subdomain and find tenant
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            $tenant = Tenant::where('slug', $subdomain)->first();
        }

        if ($tenant !== null) {
            // Add tenant to request context for logging/debugging
            Context::add('current_tenant', $tenant->name);
            Context::add('tenant_id', $tenant->id);
            Context::add('tenant_slug', $tenant->slug);

            // Set panel configuration based on tenant settings
            $this->setPanelConfiguration($tenant);

            // Update user's current_tenant_id if authenticated and different
            $auth = Auth::user();
            if ($auth && $auth->current_tenant_id !== $tenant->id) {
                // Only update if user has access to this tenant
                if ($auth->canAccessTenant($tenant)) {
                    $auth->setCurrentTenant($tenant);
                }
            }
            
            // Set Filament tenant context if available and authenticated
            if (Auth::check()) {
                try {
                    filament()->setTenant($tenant);
                } catch (\Exception $e) {
                    // Silently fail if Filament context is not available
                }
            }
        } elseif (!$isAuthPath && Auth::check()) {
            // If user is authenticated but not on a subdomain and not on auth page,
            // try to set tenant from user's current_tenant_id
            $auth = Auth::user();
            if ($auth && $auth->current_tenant_id) {
                $tenant = Tenant::find($auth->current_tenant_id);
                if ($tenant) {
                    try {
                        filament()->setTenant($tenant);
                    } catch (\Exception $e) {
                        // Silently fail
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Set panel configuration based on tenant settings.
     *
     * @param Tenant $tenant
     */
    private function setPanelConfiguration(Tenant $tenant): void
    {
        try {
            // Apply tenant's primary color to Filament panels
            if ($tenant->primary_color) {
                FilamentColor::register([
                    'primary' => $tenant->primary_color,
                    'danger' => Color::Rose,
                    'gray' => Color::Zinc,
                    'info' => Color::Blue,
                    'success' => Color::Green,
                    'warning' => Color::Amber,
                ]);
            }

            // Additional tenant-specific configurations can be added here
            // For example: locale, timezone, currency, etc.
        } catch (\Exception $e) {
            // Silently fail if Filament is not available in current context
            // This allows the middleware to work in non-Filament contexts
        }
    }
}
