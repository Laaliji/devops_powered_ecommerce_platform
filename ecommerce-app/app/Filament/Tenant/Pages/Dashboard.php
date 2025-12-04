<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    public function getWidgets(): array
    {
        return [
            \App\Filament\Tenant\Widgets\RevenueStatsWidget::class,
            \App\Filament\Tenant\Widgets\ProductStatsWidget::class,
            \App\Filament\Tenant\Widgets\OrdersChartWidget::class,
            \App\Filament\Tenant\Widgets\OrderStatusWidget::class,
            \App\Filament\Tenant\Widgets\RevenueChartWidget::class,
            \App\Filament\Tenant\Widgets\TopProductsWidget::class,
        ];
    }
    
    public function getColumns(): int | string | array
    {
        return 2;
    }
}
