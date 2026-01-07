<?php

namespace App\Filament\Resources\WorkReportResource\Pages;

use App\Filament\Resources\WorkReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkReport extends ViewRecord
{
    protected static string $resource = WorkReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('preview_work_report')
                ->label('Previsualizar PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn() => route('work-report.preview', $this->record->id))
                ->openUrlInNewTab()
                ->tooltip('Previsualizar reporte de trabajo en PDF'),
            Actions\Action::make('download_work_report_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn() => route('work-report.pdf', $this->record->id))
                ->openUrlInNewTab()
                ->tooltip('Descargar reporte de trabajo en PDF'),
            Actions\Action::make('generate_evidence_report')
                ->label('Informe de Evidencias')
                ->icon('heroicon-o-camera')
                ->color('danger')
                ->url(fn() => route('evidence-report.pdf', $this->record->id))
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->photos()->count() > 0)
                ->tooltip('Generar informe PDF con evidencias fotográficas'),
        ];
    }
}
