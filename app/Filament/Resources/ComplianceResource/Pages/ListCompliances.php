<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use App\Models\Compliance;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ListCompliances extends ListRecords
{
    protected static string $resource = ComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('selectAndDownloadActaWithReports')
                ->label('Descargar Acta + Reportes')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->form([
                    Select::make('compliance_id')
                        ->label('Seleccionar Acta de Conformidad')
                        ->options(
                            Compliance::with('project')
                                ->whereHas('project', function ($query) {
                                    $query->allowedForUser(Auth::user());
                                })
                                ->latest('id')
                                ->limit(10)
                                ->get()
                                ->mapWithKeys(fn($c) => [
                                    $c->id => "Acta #{$c->id} - {$c->project?->name}"
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->placeholder('Buscar acta por ID o proyecto...')
                        ->helperText('Mostrando las últimas 10 actas. Usa búsqueda para filtrar. Solo se listarán las actas de proyectos a los que pertenezcas.'),
                ])
                ->action(function (array $data) {
                    return redirect()->route('actas.pdf-with-reports', $data['compliance_id']);
                })
                ->modalHeading('Descargar Acta con Reportes')
                ->modalDescription('Selecciona una acta de conformidad para descargar junto con sus reportes de trabajo')
                ->modalSubmitActionLabel('Descargar')
                ->modalWidth('md'),

            Actions\CreateAction::make()
                ->label('Nueva Acta')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
