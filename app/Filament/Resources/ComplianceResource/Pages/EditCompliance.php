<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompliance extends EditRecord
{
    protected static string $resource = ComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
