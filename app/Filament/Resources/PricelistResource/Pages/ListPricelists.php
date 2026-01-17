<?php

namespace App\Filament\Resources\PricelistResource\Pages;

use App\Filament\Resources\PricelistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPricelists extends ListRecords
{
    protected static string $resource = PricelistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
