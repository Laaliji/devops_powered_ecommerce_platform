<?php

return [

    'shield_resource' => [
        'should_register_navigation' => false,
        'slug' => 'roles-permissions',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => false,
        'is_globally_searchable' => false,
        'show_model_path' => false,
        'is_scoped_to_tenant' => false,
    ],

    'auth_provider_model' => [
        'fqcn' => App\Models\User::class,
    ],

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => true,
        'intercept_gate' => 'before',
    ],

    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ],
        'page' => 'page',
        'widget' => 'widget',
    ],

    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
    ],

    'generator' => [
        'option' => 'policies_and_permissions',
        'policy_directory' => 'Policies',
    ],

    'discovery' => [
        'discover_all_resources' => true,
        'discover_all_widgets' => true,
        'discover_all_pages' => true,
    ],

];
