<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class TenantRegistrationResponse implements RegistrationResponse
{
    public function toResponse($request)
    {
        // Get the authenticated user's tenant
        $user = Auth::user();
        
        if ($user && $user->current_tenant_id) {
            $tenant = $user->currentTenant;
            
            if ($tenant) {
                // Build tenant subdomain URL
                $protocol = $request->secure() ? 'https' : 'http';
                $domain = config('app.domain', 'localhost');
                $port = '';
                
                if ($request->getPort() && 
                    !($request->secure() && $request->getPort() === 443) && 
                    !(!$request->secure() && $request->getPort() === 80)) {
                    $port = ':' . $request->getPort();
                }
                
                $url = "{$protocol}://{$tenant->slug}.{$domain}{$port}/tenant";
                
                // Return HTML with meta refresh and JavaScript redirect
                // This forces a full page navigation to the new subdomain
                $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url={$url}">
    <script>
        window.location.href = '{$url}';
    </script>
</head>
<body>
    <p>Redirecting to your tenant dashboard...</p>
</body>
</html>
HTML;
                
                return new Response($html, 200, ['Content-Type' => 'text/html']);
            }
        }
        
        // Fallback
        return redirect()->to('/tenant');
    }
}
