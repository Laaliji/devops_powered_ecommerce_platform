<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        // Total Revenue (all-time)
        $totalRevenue = Order::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');
        
        // Current Month Revenue
        $currentMonthRevenue = Order::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        
        // Last Month Revenue
        $lastMonthRevenue = Order::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total');
        
        // Calculate growth percentage
        $growthPercentage = 0;
        if ($lastMonthRevenue > 0) {
            $growthPercentage = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        }
        
        // Average Order Value
        $orderCount = Order::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();
        
        $averageOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;
        
        return [
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('All-time revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueChartData()),
            
            Stat::make('Monthly Revenue', '$' . number_format($currentMonthRevenue, 2))
                ->description(($growthPercentage >= 0 ? '+' : '') . number_format($growthPercentage, 1) . '% from last month')
                ->descriptionIcon($growthPercentage >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthPercentage >= 0 ? 'success' : 'danger'),
            
            Stat::make('Average Order Value', '$' . number_format($averageOrderValue, 2))
                ->description($orderCount . ' total orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
    
    /**
     * Get revenue data for the last 7 days for the sparkline chart
     */
    protected function getRevenueChartData(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $revenue = Order::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->whereDate('created_at', $date)
                ->sum('total');
            
            $data[] = (float) $revenue;
        }
        
        return $data;
    }
}
