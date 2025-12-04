<?php

namespace App\Filament\Tenant\Resources\CartItemResource\Pages;

use App\Filament\Tenant\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}

