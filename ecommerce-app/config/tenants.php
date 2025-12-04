<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reserved Tenant Slugs
    |--------------------------------------------------------------------------
    |
    | These slugs cannot be used as tenant subdomains as they are reserved
    | for system use, common services, or potential future features.
    |
    */
    'reserved_slugs' => [
        'admin',
        'api',
        'www',
        'mail',
        'ftp',
        'localhost',
        'tenant',
        'app',
        'dashboard',
        'support',
        'help',
        'docs',
        'blog',
        'shop',
        'store',
        'cdn',
        'static',
        'assets',
        'public',
        'private',
        'system',
        'root',
        'test',
        'staging',
        'dev',
        'demo',
        'status',
        'health',
        'metrics',
        'webhooks',
        'callback',
        'oauth',
        'auth',
        'login',
        'register',
        'signup',
        'signin',
        'logout',
        'password',
        'reset',
        'verify',
        'email',
        'sms',
        'notification',
        'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configuration for tenant slug validation including length constraints
    | and format requirements.
    |
    */
    'slug' => [
        'min_length' => 3,
        'max_length' => 30,
        'pattern' => '/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Types
    |--------------------------------------------------------------------------
    |
    | Available tenant types for categorizing different business models.
    |
    */
    'types' => [
        'ecommerce' => 'E-Commerce',
        'business' => 'Business Services',
        'consulting' => 'Consulting',
        'saas' => 'SaaS Platform',
        'marketplace' => 'Marketplace',
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tenant Settings
    |--------------------------------------------------------------------------
    |
    | Default values for new tenants.
    |
    */
    'defaults' => [
        'primary_color' => '#3B82F6',
        'type' => 'ecommerce',
    ],
];
