<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        $data = [];
        $labels = [];
        
        // Get revenue for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $revenue = Order::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');
            
            $data[] = (float) $revenue;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(147, 51, 234, 0.8)', // Purple
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    'borderColor' => 'rgba(147, 51, 234, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                        'callback' => 'function(value) { return "$" + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
