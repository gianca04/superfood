<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompliances extends ListRecords
{
    protected static string $resource = ComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
