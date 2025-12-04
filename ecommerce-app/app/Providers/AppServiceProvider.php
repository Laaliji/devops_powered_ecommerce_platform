<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Configure authorization gates following Oura pattern
        $this->configureAuthorization();
    }

    /**
     * Configure authorization gates for the application.
     * Following the Oura Roles & Shield Implementation Guide pattern.
     */
    protected function configureAuthorization(): void
    {
        // Before gate: Check super_admin first
        // If user is super_admin, allow all actions before checking policies
        Gate::before(function ($user, $ability) {
            // If no user is authenticated, deny by default
            if (!$user) {
                return false;
            }

            // Super admin can do everything
            if ($user->hasRole('super_admin')) {
                return true;
            }

            // For other users, fall through to normal policy/permission checks
            return null;
        });

        // After gate: Default deny for anything not explicitly allowed
        Gate::after(function ($user, $ability, $result) {
            // If a policy/permission explicitly allowed it, allow it
            if ($result === true) {
                return true;
            }

            // Default: deny access
            return false;
        });
    }
}
