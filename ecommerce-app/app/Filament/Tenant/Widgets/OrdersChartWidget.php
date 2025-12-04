<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Orders Trend';
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        $data = [];
        $labels = [];
        
        // Get orders for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $orderCount = Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
            
            $data[] = $orderCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'borderColor' => '#9333ea', // Purple color
                    'backgroundColor' => 'rgba(147, 51, 234, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
