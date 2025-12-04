<?php

namespace App\Filament\Tenant\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;

class Login extends BaseLogin
{
    /**
     * Custom login page for tenant panel.
     * After successful login, redirects to tenant subdomain if on base domain.
     */
    
    public function authenticate(): ?LoginResponse
    {
        // Perform standard authentication
        $response = parent::authenticate();
        
        // After successful login, check if we need to redirect to tenant subdomain
        if ($response && Auth::check()) {
            $user = Auth::user();
            $currentHost = request()->getHost();
            $baseDomain = config('app.domain', 'localhost');
            
            // If user is on base domain and has a tenant, redirect to tenant subdomain
            if ($currentHost === $baseDomain && $user->current_tenant_id) {
                $tenant = $user->currentTenant;
                if ($tenant) {
                    // Build tenant URL
                    $protocol = request()->secure() ? 'https' : 'http';
                    $port = '';
                    if (request()->getPort() && 
                        !(request()->secure() && request()->getPort() === 443) && 
                        !(!request()->secure() && request()->getPort() === 80)) {
                        $port = ':' . request()->getPort();
                    }
                    
                    $url = "{$protocol}://{$tenant->slug}.{$baseDomain}{$port}/tenant";
                    
                    return new class($url) implements LoginResponse {
                        protected string $url;
                        
                        public function __construct(string $url)
                        {
                            $this->url = $url;
                        }
                        
                        public function toResponse($request)
                        {
                            return redirect()->away($this->url);
                        }
                    };
                }
            }
        }
        
        return $response;
    }
}
