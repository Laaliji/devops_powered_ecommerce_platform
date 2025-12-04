<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as AuthLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Facades\Filament;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

/**
 * Tenant public login page - accessible without authentication or tenant context.
 * Follows Oura pattern: Auth pages bypass all Shield authorization.
 */
class Login extends AuthLogin
{
    // Don't use Shield traits
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Login';
    protected static bool $shouldRegisterBreadcrumbs = false;

    /**
     * Auth pages are always accessible - no permissions required.
     * This is critical to prevent 403 Forbidden errors.
     */
    public static function canAccess(): bool
    {
        return true;
    }

    /**
     * Don't show in navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Custom authentication that ensures proper response handling.
     */
    public function authenticate(): ?LoginResponse
    {
        // Rate limit check
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            \Filament\Notifications\Notification::make()
                ->title('Too many login attempts')
                ->body("Please try again in {$exception->secondsUntilAvailable} seconds")
                ->danger()
                ->send();

            return null;
        }

        // Get form data and authenticate
        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        // Attempt authentication using Filament
        if (!Filament::auth()->attempt($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        // Session regeneration
        session()->regenerate();

        // Use custom login response to redirect to tenant dashboard
        return app(TenantLoginResponse::class);
    }
}
