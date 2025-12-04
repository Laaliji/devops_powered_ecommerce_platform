<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        $tenantId = filament()->getTenant()->id;
        
        return $table
            ->heading('Top Selling Products')
            ->query(
                Product::query()
                    ->where('tenant_id', $tenantId)
                    ->withCount(['orderItems as units_sold' => function ($query) {
                        $query->selectRaw('COALESCE(SUM(quantity), 0)');
                    }])
                    ->withSum('orderItems as revenue', 'total')
                    ->orderByDesc('units_sold')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('units_sold')
                    ->label('Units Sold')
                    ->sortable()
                    ->default(0)
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Revenue')
                    ->money('USD')
                    ->sortable()
                    ->default(0)
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
            ])
            ->paginated(false);
    }
}
