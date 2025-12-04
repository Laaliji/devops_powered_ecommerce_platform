<?php

namespace App\Models;

use App\Traits\UserRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, UserRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_tenant_id',
        'is_active',
        'mobile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all tenants the user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withTimestamps();
    }

    /**
     * Get the user's current active tenant.
     */
    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    /**
     * Get all orders belonging to this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all invoices belonging to this user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the user's shopping cart for current tenant.
     */
    public function cart(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ShoppingCart::class, 'user_id')
            ->where('tenant_id', $this->current_tenant_id);
    }

    /**
     * Get the tenants the user can access in Filament.
     * Required by HasTenants interface.
     */
    public function getTenants(Panel $panel): Collection
    {
        // Super admins can see all tenants
        if ($this->hasRole('super_admin')) {
            return Tenant::all();
        }

        // Regular users see only their assigned tenants
        return $this->tenants;
    }

    /**
     * Check if the user can access a specific tenant.
     * Required by HasTenants interface.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        // Super admins can access all tenants
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Check if user is assigned to this tenant
        return $this->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Check if the user can access a specific Filament panel.
     * Required by FilamentUser interface.
     * 
     * Follows Oura pattern: Auth panels are public, tenant panels require tenant access.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Tenant auth panel (login/register): Always allow access
        // This is a public-facing panel with no tenant context
        if ($panel->getId() === 'tenant-auth') {
            return true;
        }

        // Admin panel: only super_admin
        if ($panel->getId() === 'admin') {
            return $this->hasRole('super_admin');
        }

        // Tenant panel: user must have at least one tenant assigned
        if ($panel->getId() === 'tenant') {
            return $this->tenants()->exists();
        }

        // Default: allow access
        return true;
    }

    /**
     * Switch the user's current tenant.
     */
    public function switchTenant(?Tenant $tenant = null): bool
    {
        if (!$tenant || !$this->canAccessTenant($tenant)) {
            return false;
        }

        $this->update(['current_tenant_id' => $tenant->id]);
        
        return true;
    }

    /**
     * Set the user's current tenant.
     */
    public function setCurrentTenant(?Tenant $tenant): void
    {
        if ($tenant && $this->current_tenant_id !== $tenant->id) {
            $this->update(['current_tenant_id' => $tenant->id]);
        }
    }
}
