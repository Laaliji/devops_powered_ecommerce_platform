<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create two main roles
        $roles = [
            'super_admin' => 'Super Admin - Manages the entire platform',
            'tenant' => 'Tenant Owner - Manages their own eCommerce store',
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
            
            $this->command->info("Created role: {$roleName}");
        }

        // Create permissions
        $permissions = [
            // Product permissions
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Order permissions
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'process_orders',
            
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Tenant permissions
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'delete_tenants',
            
            // Settings permissions
            'view_settings',
            'edit_settings',
            
            // Analytics permissions
            'view_analytics',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
        }

        $this->command->info('Created ' . count($permissions) . ' permissions');

        // Assign permissions to roles
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->givePermissionTo(Permission::all());

        $tenant = Role::findByName('tenant');
        $tenant->givePermissionTo([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_orders', 'create_orders', 'edit_orders', 'process_orders',
            'view_users', 'create_users', 'edit_users',
            'view_settings', 'edit_settings',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
