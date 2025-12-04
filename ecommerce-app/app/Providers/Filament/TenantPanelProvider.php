<?php

namespace App\Providers\Filament;

use App\Models\Tenant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('tenant')
            ->path('tenant')
            ->colors([
                'primary' => Color::Purple,
            ])
            ->tenant(Tenant::class, slugAttribute: 'slug', ownershipRelationship: 'tenants')
            // Removed tenantDomain to allow access from both base domain and subdomains
            // Custom login and registration pages
            ->login(\App\Filament\Tenant\Pages\Auth\Login::class)
            ->registration(\App\Filament\Pages\Auth\Register::class)  // Using wizard-based registration
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\\Filament\\Tenant\\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\\Filament\\Tenant\\Pages')
            ->pages([
                \App\Filament\Tenant\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\\Filament\\Tenant\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetTenant::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
