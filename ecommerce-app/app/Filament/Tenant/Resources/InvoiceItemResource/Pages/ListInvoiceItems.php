<?php

namespace App\Filament\Tenant\Resources\InvoiceItemResource\Pages;

use App\Filament\Tenant\Resources\InvoiceItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceItems extends ListRecords
{
    protected static string $resource = InvoiceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

