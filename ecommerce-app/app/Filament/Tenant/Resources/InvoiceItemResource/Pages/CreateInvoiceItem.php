<?php

namespace App\Filament\Tenant\Resources\InvoiceItemResource\Pages;

use App\Filament\Tenant\Resources\InvoiceItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoiceItem extends CreateRecord
{
    protected static string $resource = InvoiceItemResource::class;
}

