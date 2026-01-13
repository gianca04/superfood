<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompliance extends CreateRecord
{
    protected static string $resource = ComplianceResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
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
