<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Tenant extends Model
{
    use SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'primary_color',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all users belonging to this tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withTimestamps();
    }

    /**
     * Get the tenant owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get employees (users with tenant role) for this tenant.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'tenant');
            })
            ->withTimestamps();
    }

    /**
     * Get admins for this tenant.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'tenant');
            })
            ->withTimestamps();
    }

    /**
     * Get clients (customers) for this tenant.
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->withTimestamps();
    }

    /**
     * Get all products belonging to this tenant.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all categories belonging to this tenant.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    /**
     * Get all orders belonging to this tenant.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all invoices belonging to this tenant.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all vendors belonging to this tenant.
     */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    /**
     * Get all shopping carts belonging to this tenant.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(ShoppingCart::class);
    }

    /**
     * Switch to this tenant in Filament context.
     */
    public function switch(): void
    {
        if (filament()->hasTenancy()) {
            filament()->setTenant($this);
        }
    }

    /**
     * Get the tenant's full subdomain URL.
     * 
     * Constructs the full URL with subdomain based on:
     * - Protocol (http/https)
     * - Tenant slug (subdomain)
     * - Base domain from config
     * 
     * Example: https://tech-solutions.oura.local
     */
    public function getUrlAttribute(): string
    {
        try {
            $protocol = request()->secure() ? 'https' : 'http';
        } catch (\Exception $e) {
            // If not in a request context, default to http
            $protocol = 'http';
        }
        
        $domain = config('app.domain', 'localhost');
        
        return "{$protocol}://{$this->slug}.{$domain}";
    }

    /**
     * Get the tenant type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $types = config('tenants.types', []);
        return $types[$this->type] ?? ucfirst($this->type);
    }
}
