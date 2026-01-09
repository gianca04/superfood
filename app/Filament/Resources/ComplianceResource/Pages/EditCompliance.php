<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompliance extends EditRecord
{
    protected static string $resource = ComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Reemplazar el ID del proyecto con su nombre
        if (isset($data['project_id'])) {
            $project = Project::find($data['project_id']);
            if ($project) {
                $data['project_id'] = $project->name; // Cambiar el ID por el nombre
            }
        }

        return $data;
    }
}
