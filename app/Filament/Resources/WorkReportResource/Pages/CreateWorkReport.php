<?php

namespace App\Filament\Resources\WorkReportResource\Pages;

use App\Filament\Resources\WorkReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkReport extends CreateRecord
{
    protected static string $resource = WorkReportResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Capturar project_id de la URL si existe
        $projectId = request()->query('project_id');
        if ($projectId) {
            $data['project_id'] = $projectId;
            session(['project_id' => $projectId]);
        }

        return $data;
    }
}
