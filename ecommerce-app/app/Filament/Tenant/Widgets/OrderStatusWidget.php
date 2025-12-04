<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Orders by Status';
    protected static ?int $sort = 6;
    
    protected function getData(): array
    {
        $tenantId = filament()->getTenant()->id;
        
        // Get order counts by status
        $pending = Order::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();
        
        $processing = Order::where('tenant_id', $tenantId)
            ->where('status', 'processing')
            ->count();
        
        $completed = Order::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->count();
        
        $cancelled = Order::where('tenant_id', $tenantId)
            ->whereIn('status', ['cancelled', 'refunded'])
            ->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => [$pending, $processing, $completed, $cancelled],
                    'backgroundColor' => [
                        'rgba(234, 179, 8, 0.8)',  // Yellow for pending
                        'rgba(59, 130, 246, 0.8)', // Blue for processing
                        'rgba(34, 197, 94, 0.8)',  // Green for completed
                        'rgba(239, 68, 68, 0.8)',  // Red for cancelled
                    ],
                    'borderColor' => [
                        'rgba(234, 179, 8, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pending', 'Processing', 'Completed', 'Cancelled'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
