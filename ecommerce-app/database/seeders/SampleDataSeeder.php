<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->error('No tenant found. Run AdminUserSeeder first.');
            return;
        }

        // Create categories
        $electronics = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and accessories',
        ]);

        $clothing = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Fashion and apparel',
        ]);

        // Create products
        Product::create([
            'tenant_id' => $tenant->id,
            'product_category_id' => $electronics->id,
            'name' => 'Laptop',
            'slug' => 'laptop',
            'sku' => 'LAP-001',
            'description' => 'High-performance laptop',
            'price' => 999.99,
            'cost' => 700.00,
            'stock_quantity' => 50,
            'status' => 'active',
        ]);

        Product::create([
            'tenant_id' => $tenant->id,
            'product_category_id' => $electronics->id,
            'name' => 'Smartphone',
            'slug' => 'smartphone',
            'sku' => 'PHN-001',
            'description' => 'Latest smartphone model',
            'price' => 699.99,
            'cost' => 500.00,
            'stock_quantity' => 100,
            'status' => 'active',
        ]);

        Product::create([
            'tenant_id' => $tenant->id,
            'product_category_id' => $clothing->id,
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSH-001',
            'description' => 'Cotton t-shirt',
            'price' => 19.99,
            'cost' => 10.00,
            'stock_quantity' => 200,
            'status' => 'active',
        ]);

        // Create vendors
        Vendor::create([
            'tenant_id' => $tenant->id,
            'name' => 'Tech Supplier Inc',
            'email' => 'contact@techsupplier.com',
            'phone' => '555-0100',
            'address' => '123 Tech Street',
            'is_active' => true,
        ]);

        Vendor::create([
            'tenant_id' => $tenant->id,
            'name' => 'Fashion Wholesale',
            'email' => 'info@fashionwholesale.com',
            'phone' => '555-0200',
            'address' => '456 Fashion Ave',
            'is_active' => true,
        ]);

        $this->command->info('Sample data created successfully!');
    }
}
