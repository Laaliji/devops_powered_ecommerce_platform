<?php

namespace App\Traits;

trait UserRoles
{
    /**
     * Check if the user is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a tenant admin/owner.
     *
     * @return bool
     */
    public function isTenantAdmin(): bool
    {
        return $this->hasRole('tenant');
    }

    /**
     * Check if the user is a tenant employee (same as tenant for simplified structure).
     *
     * @return bool
     */
    public function isTenantEmployee(): bool
    {
        return $this->hasRole('tenant');
    }

    /**
     * Check if the user is a sales employee (same as tenant for simplified structure).
     *
     * @return bool
     */
    public function isSalesEmployee(): bool
    {
        return $this->hasRole('tenant');
    }

    /**
     * Check if the user is a client/customer.
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Check if the user has any admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get the user's primary role.
     *
     * @return string|null
     */
    public function getPrimaryRole(): ?string
    {
        $role = $this->roles()->first();
        return $role ? $role->name : null;
    }

    /**
     * Check if user belongs to a specific tenant.
     *
     * @param \App\Models\Tenant|int|null $tenant
     * @return bool
     */
    public function belongsToTenant($tenant = null): bool
    {
        if (!$tenant) {
            $tenant = tenant();
        }

        if (!$tenant) {
            return false;
        }

        $tenantId = $tenant instanceof \App\Models\Tenant ? $tenant->id : $tenant;

        // Super admins can access all tenants
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if current_tenant_id matches
        if ($this->current_tenant_id === $tenantId) {
            return true;
        }

        // Check if user is assigned to this tenant
        return $this->tenants()->where('tenants.id', $tenantId)->exists();
    }
}
