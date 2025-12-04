<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant from Filament context or subdomain.
     *
     * @param string|null $attr Optional attribute to retrieve from tenant
     * @return Tenant|string|null
     */
    function tenant(?string $attr = null): Tenant|string|null
    {
        // First, try to get tenant from Filament context
        try {
            $tenant = filament()->getTenant();
        } catch (\Exception $e) {
            $tenant = null;
        }

        // If no tenant from Filament, try to resolve from subdomain
        if (!$tenant) {
            try {
                $host = request()->getHost();
                $baseDomain = config('app.domain', 'localhost');

                // Check if we're on a subdomain
                if ($host !== $baseDomain && str_ends_with($host, '.' . $baseDomain)) {
                    $subdomain = str_replace('.' . $baseDomain, '', $host);
                    
                    // Try to find tenant by slug
                    $tenant = Tenant::where('slug', $subdomain)->first();

                    // If found, set it as the current Filament tenant
                    // Only set tenant if user is authenticated (filament()->setTenant() requires auth user)
                    if ($tenant && filament()->hasTenancy() && Auth::check()) {
                        try {
                            filament()->setTenant($tenant);
                        } catch (\Exception $e) {
                            // Silently fail if we can't set tenant in current context
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silently fail if we can't resolve tenant
                $tenant = null;
            }
        }

        // Return specific attribute if requested
        return $attr && $tenant ? $tenant->$attr : $tenant;
    }
}

if (!function_exists('user')) {
    /**
     * Get the currently authenticated user.
     *
     * @return User|null
     */
    function user(): ?User
    {
        return Auth::user();
    }
}

if (!function_exists('currentTenant')) {
    /**
     * Get the current user's active tenant.
     *
     * @return Tenant|null
     */
    function currentTenant(): ?Tenant
    {
        $user = user();
        
        if (!$user) {
            return null;
        }

        return $user->currentTenant;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the current user is an admin.
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        $user = user();
        
        if (!$user) {
            return false;
        }

        return $user->hasRole('admin');
    }
}

if (!function_exists('isTenantAdmin')) {
    /**
     * Check if the current user is a tenant owner.
     *
     * @return bool
     */
    function isTenantAdmin(): bool
    {
        $user = user();
        
        if (!$user) {
            return false;
        }

        return $user->hasRole('tenant');
    }
}

if (!function_exists('canAccessTenant')) {
    /**
     * Check if the current user can access a specific tenant.
     *
     * @param Tenant|int $tenant
     * @return bool
     */
    function canAccessTenant(Tenant|int $tenant): bool
    {
        $user = user();
        
        if (!$user) {
            return false;
        }

        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        // Admins can access all tenants
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if user is assigned to this tenant
        return $user->tenants()->where('tenants.id', $tenantId)->exists();
    }
}
