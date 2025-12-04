<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for system administrators and admin panel.
    |
    */
    'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Role Definitions
    |--------------------------------------------------------------------------
    |
    | Define the different role categories used throughout the application.
    |
    */
    
    // Roles that have admin-level access
    'admin_roles' => [
        'super_admin',
        'tenant-admin',
    ],
    
    // Roles specific to tenant management
    'tenant_roles' => [
        'tenant-admin',
    ],
    
    // All employee roles (tenant-level)
    'employee_roles' => [
        'tenant-admin',
        'sales-manager',
        'inventory-manager',
        'customer-support',
    ],
    
    // Customer/client roles
    'client_roles' => [
        'client',
    ],
    
    // Roles that should be excluded from user management interfaces
    'excluded_roles_for_user' => [
        'super_admin',
        'client',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    |
    | Organize permissions into logical groups for easier management.
    |
    */
    'permission_groups' => [
        'products' => [
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
        ],
        'orders' => [
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'process_orders',
        ],
        'users' => [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
        ],
        'tenants' => [
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'delete_tenants',
        ],
        'settings' => [
            'view_settings',
            'edit_settings',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | General application-wide settings.
    |
    */
    'app_name' => env('APP_NAME', 'eCommerce Platform'),
    'items_per_page' => 15,
    'max_upload_size' => 5120, // KB
];
