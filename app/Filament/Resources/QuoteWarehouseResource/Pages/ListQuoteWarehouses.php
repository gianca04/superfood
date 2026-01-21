<?php

namespace App\Filament\Resources\QuoteWarehouseResource\Pages;

use App\Filament\Resources\QuoteWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuoteWarehouses extends ListRecords
{
    protected static string $resource = QuoteWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
