<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;

/**
 * Base auth page class that properly bypasses Shield authorization.
 * All auth pages should extend this instead of directly extending Filament auth pages.
 */
class AuthPage extends Page
{
    /**
     * Override to prevent Shield from checking permissions on auth pages.
     * Auth pages are inherently public-facing.
     */
    public static function canAccess(): bool
    {
        return true;
    }

    /**
     * Don't show auth pages in navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Auth pages don't have breadcrumbs.
     */
    protected static bool $shouldRegisterBreadcrumbs = false;
}
