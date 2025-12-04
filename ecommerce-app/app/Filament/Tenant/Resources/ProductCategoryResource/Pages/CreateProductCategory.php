<?php

namespace App\Filament\Tenant\Resources\ProductCategoryResource\Pages;

use App\Filament\Tenant\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;
}

