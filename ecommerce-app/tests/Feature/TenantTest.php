<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_be_created(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Corporation',
            'slug' => 'test-corporation',
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Corporation',
            'slug' => 'test-corporation',
        ]);
    }

    public function test_tenant_slug_is_unique(): void
    {
        Tenant::factory()->create(['slug' => 'unique-slug']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Tenant::factory()->create(['slug' => 'unique-slug']);
    }

    public function test_user_can_belong_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        $user->tenants()->attach($tenant);

        $this->assertTrue($user->tenants->contains($tenant));
        $this->assertTrue($tenant->users->contains($user));
    }
}
