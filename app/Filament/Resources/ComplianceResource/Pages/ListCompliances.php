<?php

namespace App\Filament\Resources\ComplianceResource\Pages;

use App\Filament\Resources\ComplianceResource;
use App\Models\Compliance;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListCompliances extends ListRecords
{
    protected static string $resource = ComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Exportar Excel individual
            Actions\Action::make('exportExcel')
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('compliance_id')
                        ->label('Seleccionar Acta')
                        ->options(Compliance::with('project')->get()->pluck('project.name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Seleccione el acta que desea exportar'),
                ])
                ->action(function (array $data) {
                    return redirect()->to(route('actas.excel', $data['compliance_id']));
                })
                ->modalHeading('Exportar Acta a Excel')
                ->modalSubmitActionLabel('Descargar Excel')
                ->modalIcon('heroicon-o-document-arrow-down'),

            // Exportar PDF individual
            Actions\Action::make('exportPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->form([
                    Select::make('compliance_id')
                        ->label('Seleccionar Acta')
                        ->options(Compliance::with('project')->get()->pluck('project.name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Seleccione el acta que desea exportar'),
                ])
                ->action(function (array $data) {
                    return redirect()->to(route('actas.pdf', $data['compliance_id']));
                })
                ->modalHeading('Exportar Acta a PDF')
                ->modalSubmitActionLabel('Descargar PDF')
                ->modalIcon('heroicon-o-document-text'),

            // Vista Previa
            Actions\Action::make('preview')
                ->label('Vista Previa')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->form([
                    Select::make('compliance_id')
                        ->label('Seleccionar Acta')
                        ->options(Compliance::with('project')->get()->pluck('project.name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Seleccione el acta que desea previsualizar'),
                ])
                ->action(function (array $data) {
                    return redirect()->to(route('actas.preview', $data['compliance_id']));
                })
                ->modalHeading('Vista Previa de Acta')
                ->modalSubmitActionLabel('Ver Vista Previa')
                ->modalIcon('heroicon-o-eye'),

            Actions\CreateAction::make()
                ->label('Nueva Acta')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
