<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAndTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating system admin...');

        // Create System Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'mobile' => '+1234567890',
            ]
        );
        $admin->assignRole('super_admin');
        
        $this->command->info("✓ Admin created: {$admin->email} / password");

        // Create Tenant #1: Tech Solutions
        $this->command->info('Creating Tenant #1: Tech Solutions...');
        
        $tenantAdmin1 = User::firstOrCreate(
            ['email' => 'tech-admin@example.com'],
            [
                'name' => 'Ahmed Mohammed',
                'password' => Hash::make('password'),
                'is_active' => true,
                'mobile' => '+1234567891',
            ]
        );
        $tenantAdmin1->assignRole('tenant');

        $tenant1 = Tenant::firstOrCreate(
            ['slug' => 'tech-solutions'],
            [
                'name' => 'Tech Solutions Inc.',
                'type' => 'business',
                'primary_color' => '#3B82F6',
                'user_id' => $tenantAdmin1->id,
            ]
        );

        // Attach user to tenant
        if (!$tenant1->users()->where('user_id', $tenantAdmin1->id)->exists()) {
            $tenant1->users()->attach($tenantAdmin1->id);
        }
        $tenantAdmin1->update(['current_tenant_id' => $tenant1->id]);

        $this->command->info("✓ Tenant created: {$tenant1->name} ({$tenant1->slug})");
        $this->command->info("  Owner: {$tenantAdmin1->email} / password");

        // Create Tenant #2: E-Commerce Store
        $this->command->info('Creating Tenant #2: E-Commerce Store...');
        
        $tenantAdmin2 = User::firstOrCreate(
            ['email' => 'ecommerce-admin@example.com'],
            [
                'name' => 'Khaled Nasser',
                'password' => Hash::make('password'),
                'is_active' => true,
                'mobile' => '+1234567893',
            ]
        );
        $tenantAdmin2->assignRole('tenant');

        $tenant2 = Tenant::firstOrCreate(
            ['slug' => 'ecommerce-store'],
            [
                'name' => 'E-Commerce Store',
                'type' => 'ecommerce',
                'primary_color' => '#10B981',
                'user_id' => $tenantAdmin2->id,
            ]
        );

        if (!$tenant2->users()->where('user_id', $tenantAdmin2->id)->exists()) {
            $tenant2->users()->attach($tenantAdmin2->id);
        }
        $tenantAdmin2->update(['current_tenant_id' => $tenant2->id]);

        $this->command->info("✓ Tenant created: {$tenant2->name} ({$tenant2->slug})");
        $this->command->info("  Owner: {$tenantAdmin2->email} / password");

        // Create Tenant #3: Consulting Services
        $this->command->info('Creating Tenant #3: Consulting Services...');
        
        $tenantAdmin3 = User::firstOrCreate(
            ['email' => 'consulting-admin@example.com'],
            [
                'name' => 'Maria Garcia',
                'password' => Hash::make('password'),
                'is_active' => true,
                'mobile' => '+1234567894',
            ]
        );
        $tenantAdmin3->assignRole('tenant');

        $tenant3 = Tenant::firstOrCreate(
            ['slug' => 'consulting-services'],
            [
                'name' => 'Consulting Services Pro',
                'type' => 'consulting',
                'primary_color' => '#8B5CF6',
                'user_id' => $tenantAdmin3->id,
            ]
        );

        if (!$tenant3->users()->where('user_id', $tenantAdmin3->id)->exists()) {
            $tenant3->users()->attach($tenantAdmin3->id);
        }
        $tenantAdmin3->update(['current_tenant_id' => $tenant3->id]);

        $this->command->info("✓ Tenant created: {$tenant3->name} ({$tenant3->slug})");
        $this->command->info("  Owner: {$tenantAdmin3->email} / password");

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('Demo Data Created Successfully!');
        $this->command->info('========================================');
        $this->command->newLine();
        $this->command->info('Login Credentials (all passwords: password):');
        $this->command->info('');
        $this->command->info('Super Admin:');
        $this->command->info("  URL: http://ecommerce.local:8000/admin");
        $this->command->info("  Email: admin@example.com");
        $this->command->info('');
        $this->command->info('Tenant #1 - Tech Solutions:');
        $this->command->info("  URL: http://tech-solutions.ecommerce.local:8000/tenant");
        $this->command->info("  Admin: tech-admin@example.com");
        $this->command->info('');
        $this->command->info('Tenant #2 - E-Commerce Store:');
        $this->command->info("  URL: http://ecommerce-store.ecommerce.local:8000/tenant");
        $this->command->info("  Admin: ecommerce-admin@example.com");
        $this->command->info('');
        $this->command->info('Tenant #3 - Consulting Services:');
        $this->command->info("  URL: http://consulting-services.ecommerce.local:8000/tenant");
        $this->command->info("  Admin: consulting-admin@example.com");
        $this->command->info('========================================');
    }
}
