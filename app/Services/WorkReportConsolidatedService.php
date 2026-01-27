<?php

namespace App\Services;

use App\Models\Project;
use App\Models\WorkReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Log;

class WorkReportConsolidatedService
{
    /**
     * Genera un PDF consolidado con todos los reportes de trabajo de un proyecto
     *
     * @param int $projectId
     * @return \Barryvdh\DomPDF\PDF
     */

    public function generateConsolidatedPdf(int $projectId): \Barryvdh\DomPDF\PDF
    {
        // 1. Obtener datos base
        $project = $this->getProjectWithRelations($projectId);
        $workReports = $this->getProjectWorkReports($projectId);
        $allPhotos = $this->getAllPhotosFromReports($workReports);

        // 2. Instanciar el controlador para usar sus métodos
        $excelController = app(\App\Http\Controllers\WorkReportExcelController::class);

        // 3. Inicializar contenedores para el consolidado
        // $allToolsAndMaterials = [];
        $allPersonnel = [];
        $totalHoursConsolidated = 0;

        foreach ($workReports as $report) {
            // Procesar Herramientas y Materiales
            /*
            // Se asume que $report->tools y $report->materials son arrays (o json casted)
            $processedTools = $excelController->processToolsAndMaterials(
                $report->tools ?? [],
                $report->materials ?? []
            );
            $allToolsAndMaterials = array_merge($allToolsAndMaterials, $processedTools);
            */

            // Procesar Personal
            $processedPersonnelData = $excelController->processPersonnelForPdf(
                $report->personnel ?? []
            );

            $allPersonnel = array_merge($allPersonnel, $processedPersonnelData['personnel']);
            $totalHoursConsolidated += $processedPersonnelData['totalHours'];
        }

        Log::info('Generando PDF consolidado', [
            'project_id' => $projectId,
            'work_reports_count' => $workReports->count(),
            'total_photos' => $allPhotos->count()
        ]);

        // 4. Generar el PDF con los nombres de variables CORRECTOS
        $pdf = Pdf::loadView('reports.work-report-consolidated-pdf', [
            'project' => $project,
            'workReports' => $workReports,
            'allPhotos' => $allPhotos,
            'generatedAt' => now(),
            // 'toolsAndMaterials' => $allToolsAndMaterials, // Variable corregida
            'personnelList' => $allPersonnel,             // Variable corregida
            'totalHours' => $totalHoursConsolidated        // Dato extra útil
        ]);

        // 5. Configuración (Se mantiene igual)
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'dpi' => 150,
            'defaultPaperSize' => 'a4',
            'enable_php' => true,
            'enable_javascript' => false,
            'enable_remote' => true, // Cambiado a true por si las fotos son URLs
            'enable_html5_parser' => true,
            'chroot' => [
                public_path('storage'),
                public_path('images'),
                storage_path('app/public')
            ]
        ]);

        return $pdf;
    }

    /**
     * Obtiene el proyecto con todas sus relaciones necesarias
     *
     * @param int $projectId
     * @return Project
     */
    public function getProjectWithRelations(int $projectId): Project
    {
        return Project::with([
            'subClient.client',
            'quote',
            'workReports.employee',
            'workReports.photos'
        ])->findOrFail($projectId);
    }

    /**
     * Obtiene todos los reportes de trabajo del proyecto ordenados por fecha
     *
     * @param int $projectId
     * @return Collection
     */
    public function getProjectWorkReports(int $projectId): Collection
    {
        return WorkReport::with(['employee', 'photos'])
            ->where('project_id', $projectId)
            ->orderBy('report_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Obtiene todas las fotos de todos los reportes
     *
     * @param Collection $workReports
     * @return SupportCollection
     */
    private function getAllPhotosFromReports(Collection $workReports): SupportCollection
    {
        return $workReports->pluck('photos')->flatten();
    }

    /**
     * Genera el nombre del archivo para el PDF consolidado
     *
     * @param Project $project
     * @return string
     */
    public function generateConsolidatedFilename(Project $project): string
    {
        $clientName = $project->subClient->client->business_name ?? 'Cliente';
        $projectName = $project->name ?? 'Proyecto';
        $date = now()->format('Y-m-d');

        // Limpiar caracteres especiales para el nombre del archivo
        $clientName = $this->sanitizeFilename($clientName);
        $projectName = $this->sanitizeFilename($projectName);

        return "reporte-consolidado-{$clientName}-{$projectName}-{$date}.pdf";
    }

    /**
     * Limpia el nombre del archivo de caracteres especiales
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Reemplazar caracteres especiales y espacios
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $filename);
        // Eliminar múltiples guiones seguidos
        $filename = preg_replace('/-+/', '-', $filename);
        // Eliminar guiones al inicio y final
        return trim($filename, '-');
    }

    /**
     * Obtiene estadísticas del reporte consolidado
     *
     * @param int $projectId
     * @return array
     */
    public function getConsolidatedStatistics(int $projectId): array
    {
        $workReports = $this->getProjectWorkReports($projectId);
        $project = $this->getProjectWithRelations($projectId);

        $totalPhotos = $workReports->sum(function ($report) {
            return $report->photos->count();
        });

        $dateRange = [
            'start' => $workReports->min('report_date'),
            'end' => $workReports->max('report_date')
        ];

        $totalWorkingHours = $workReports->sum(function ($report) {
            if ($report->start_time && $report->end_time) {
                $start = \Carbon\Carbon::parse($report->start_time);
                $end = \Carbon\Carbon::parse($report->end_time);
                return $end->diffInHours($start);
            }
            return 0;
        });

        return [
            'project_name' => $project->name,
            'client_name' => $project->subClient->client->business_name ?? 'N/A',
            'total_reports' => $workReports->count(),
            'total_photos' => $totalPhotos,
            'date_range' => $dateRange,
            'total_working_hours' => $totalWorkingHours,
            'employees_involved' => $workReports->pluck('employee.full_name')->unique()->values()->toArray()
        ];
    }
}
