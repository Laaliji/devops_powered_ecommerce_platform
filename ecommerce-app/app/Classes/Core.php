<?php

namespace App\Classes;

use App\Models\Tenant;
use Filament\Facades\Filament;

class Core
{
    /**
     * Get the current Filament panel ID.
     *
     * @return string|null
     */
    public static function currentPanel(): ?string
    {
        return Filament::getCurrentPanel()?->getId();
    }

    /**
     * Check if we are currently on the admin panel.
     *
     * @return bool
     */
    public static function weAreOnAdminPanel(): bool
    {
        return self::currentPanel() === 'admin';
    }

    /**
     * Check if we are currently on the tenant panel.
     *
     * @return bool
     */
    public static function weAreOnTenantPanel(): bool
    {
        return self::currentPanel() === 'tenant';
    }

    /**
     * Get the current tenant (proxy to tenant() helper).
     *
     * @param string|null $attr
     * @return Tenant|string|null
     */
    public static function tenant(?string $attr = null): Tenant|string|null
    {
        return tenant($attr);
    }

    /**
     * Get the current user (proxy to user() helper).
     *
     * @return \App\Models\User|null
     */
    public static function user(): ?\App\Models\User
    {
        return user();
    }

    /**
     * Check if the current user is a super admin.
     *
     * @return bool
     */
    public static function isSuperAdmin(): bool
    {
        return isSuperAdmin();
    }

    /**
     * Check if the current user is a tenant admin.
     *
     * @return bool
     */
    public static function isTenantAdmin(): bool
    {
        return isTenantAdmin();
    }

    /**
     * Get the base domain from configuration.
     *
     * @return string
     */
    public static function baseDomain(): string
    {
        return config('app.domain', 'localhost');
    }

    /**
     * Get the full URL for a tenant subdomain.
     *
     * @param string $slug
     * @param string|null $path
     * @return string
     */
    public static function tenantUrl(string $slug, ?string $path = null): string
    {
        $protocol = 'http';
        $domain = 'localhost';
        
        try {
            $protocol = request()->secure() ? 'https' : 'http';
            $domain = self::baseDomain();
        } catch (\Exception $e) {
            // Use defaults
        }
        
        $url = "{$protocol}://{$slug}.{$domain}";

        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Create a phone input field component.
     *
     * @param string $fieldName
     * @param string $label
     * @return \Ysfkaya\FilamentPhoneInput\Forms\PhoneInput
     */
    public static function phoneInput(string $fieldName, string $label = '')
    {
        return \Ysfkaya\FilamentPhoneInput\Forms\PhoneInput::make($fieldName)
            ->label($label);
    }
}
