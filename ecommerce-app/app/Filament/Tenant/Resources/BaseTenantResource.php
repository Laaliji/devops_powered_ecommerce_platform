<?php

namespace App\Filament\Tenant\Resources;

use Filament\Resources\Resource as BaseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

abstract class BaseTenantResource extends BaseResource
{
    /**
     * Disable Filament's automatic tenant ownership relationship check
     * since our models use tenant_id directly, not a many-to-many relationship
     */
    protected static bool $isScopedToTenant = false;
    
    /**
     * Automatically scope all queries to the current tenant
     * Only applies if the model's table has a tenant_id column
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Get current tenant from Filament
        $tenant = filament()->getTenant();
        
        if ($tenant) {
            // Get the model instance to check its table
            $model = $query->getModel();
            $table = $model->getTable();
            
            // Only apply tenant_id scoping if the column exists
            if (Schema::hasColumn($table, 'tenant_id')) {
                $query->where('tenant_id', $tenant->id);
            }
        }
        
        return $query;
    }
}
