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
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Actualiza el estado del proyecto relacionado
        if (!empty($data['project_id']) && !empty($data['state'])) {
            $project = Project::find($data['project_id']);
            if ($project) {
                $project->status = $data['state'];
                $project->save();
            }
        }
        return $data;
    }
}
