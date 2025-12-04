<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected function getStats(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        // Total Products
        $totalProducts = Product::where('tenant_id', $tenantId)->count();
        
        // Low Stock Products (stock < 10)
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<', 10)
            ->count();
        
        // Out of Stock Products
        $outOfStockProducts = Product::where('tenant_id', $tenantId)
            ->where('stock_quantity', 0)
            ->count();
        
        // Active Products
        $activeProducts = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();
        
        return [
            Stat::make('Total Products', number_format($totalProducts))
                ->description($activeProducts . ' active products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            
            Stat::make('Low Stock Alert', number_format($lowStockProducts))
                ->description('Products with stock < 10')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            
            Stat::make('Out of Stock', number_format($outOfStockProducts))
                ->description('Requires restocking')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
