<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;

class TenantLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        // Get the authenticated user
        $user = Filament::auth()->user();
        
        // If user has a tenant, redirect to tenant dashboard
        if ($user && $user->currentTenant()) {
            $tenant = $user->currentTenant();
            // Redirect to the tenant panel dashboard on the subdomain
            $url = $tenant->url . '/tenant';
            return redirect()->to($url);
        }

        // If user is super_admin, redirect to admin panel
        if ($user && $user->hasRole('super_admin')) {
            return redirect()->intended(Filament::getUrl());
        }

        // Fallback to home
        return redirect()->to('/');
    }
}
