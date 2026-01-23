<?php

namespace App\Filament\Resources\QuoteWarehouseResource\Pages;

use App\Filament\Resources\QuoteWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuoteWarehouse extends EditRecord
{
    protected static string $resource = QuoteWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
