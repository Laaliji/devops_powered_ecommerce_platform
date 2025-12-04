<?php

namespace App\Filament\Tenant\Resources\OrderItemResource\Pages;

use App\Filament\Tenant\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;
}

