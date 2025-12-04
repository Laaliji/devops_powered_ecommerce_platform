<?php

namespace App\Filament\Tenant\Resources\ShoppingCartResource\Pages;

use App\Filament\Tenant\Resources\ShoppingCartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShoppingCart extends CreateRecord
{
    protected static string $resource = ShoppingCartResource::class;
}

