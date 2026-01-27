<?php

namespace App\Services;

use App\Models\WorkReport;
use App\Models\Employee;
use App\Models\Position;
use App\Jobs\GenerateWorkReportPdfJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class WorkReportPdfService
{
    /**
     * Configuración por defecto para PDFs
     */
    private const PDF_OPTIONS = [
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => true,
        'dpi' => 150,
        'defaultPaperSize' => 'a4',
        'enable_php' => true,
        'enable_javascript' => false,
        'enable_remote' => false,
        'enable_html5_parser' => true,
    ];

    /**
     * Genera un PDF de forma síncrona
     *
     * @param int $workReportId
     * @param array $options
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateSync(int $workReportId, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $workReport = $this->getWorkReportWithRelations($workReportId);
        $this->validateWorkReportData($workReport);

        $data = $this->prepareViewData($workReport);

        return $this->createCombinedPdf($data, $options);
    }

    /**
     * Genera un PDF de forma asíncrona usando Jobs
     *
     * @param int $workReportId
     * @param string|null $userEmail
     * @param bool $shouldEmail
     * @return void
     */
    public function generateAsync(int $workReportId, ?string $userEmail = null, bool $shouldEmail = false): void
    {
        // TODO: Implementar Job para generación asíncrona si es necesario
        Log::info('Generación asíncrona solicitada', [
            'work_report_id' => $workReportId,
            'user_email' => $userEmail,
            'should_email' => $shouldEmail
        ]);
    }

    /**
     * Obtiene el reporte con relaciones optimizadas
     *
     * @param int $workReportId
     * @return WorkReport
     */
    public function getWorkReportWithRelations(int $workReportId): WorkReport
    {
        return WorkReport::with([
            'employee:id,first_name,last_name',
            'project:id,name,end_date,sub_client_id,quote_id',
            'project.subClient:id,name,client_id',
            'project.subClient.client:id,business_name,document_number,logo',
            'project.quote:id,TDR',
            'photos' => function ($query) {
                $query->select([
                    'id',
                    'work_report_id',
                    'photo_path',
                    'before_work_photo_path',
                    'descripcion',
                    'before_work_descripcion',
                    'created_at'
                ])->orderBy('created_at', 'asc');
            }
        ])->findOrFail($workReportId);
    }

    /**
     * Valida los datos del reporte
     *
     * @param WorkReport $workReport
     * @throws \Exception
     */
    public function validateWorkReportData(WorkReport $workReport): void
    {
        if (!$workReport->employee) {
            throw new \Exception('El reporte debe tener un empleado asignado');
        }

        if (!$workReport->project) {
            throw new \Exception('El reporte debe estar asociado a un proyecto');
        }
    }

    /**
     * Prepara los datos para las vistas
     *
     * @param WorkReport $workReport
     * @return array
     */
    public function prepareViewData(WorkReport $workReport): array
    {
        // Procesar herramientas y materiales (JSON -> array estructurado)
        /*
        $toolsAndMaterials = $this->processToolsAndMaterials(
            $workReport->tools ?? [],
            $workReport->materials ?? []
        );
        */
        $toolsAndMaterials = []; // Temporalmente vacío mientras se comenta la lógica

        // Procesar personal con nombres y cargos resueltos
        $personnelData = $this->processPersonnelForPdf($workReport->personnel ?? []);

        return [
            'workReport' => $workReport,
            'employee' => $workReport->employee,
            'project' => $workReport->project,
            'photos' => $workReport->photos,
            'generatedAt' => now(),
            // Nuevos campos procesados
            // 'toolsAndMaterials' => $toolsAndMaterials,
            'personnelList' => $personnelData['personnel'],
            'totalHours' => $personnelData['totalHours'],
        ];
    }

    /*
    private function processToolsAndMaterials(array $tools, array $materials): array
    {
        $combined = [];

        // Agregar herramientas
        foreach ($tools as $tool) {
            $combined[] = [
                'nombre' => $tool['herramienta'] ?? '',
                'unidad' => $tool['unidad'] ?? '',
                'cantidad' => $tool['cantidad'] ?? '',
                'tipo' => 'herramienta',
            ];
        }

        // Agregar materiales
        foreach ($materials as $material) {
            $combined[] = [
                'nombre' => $material['material'] ?? '',
                'unidad' => $material['unidad'] ?? '',
                'cantidad' => $material['cantidad'] ?? '',
                'tipo' => 'material',
            ];
        }

        return $combined;
    }
    */

    /**
     * Procesa el array de personal resolviendo nombres de empleados y cargos
     *
     * @param array $personnel Array de personal [{"employee_id":3,"hh":"2","position_id":"3"}]
     * @return array ['personnel' => array, 'totalHours' => float]
     */
    private function processPersonnelForPdf(array $personnel): array
    {
        $processedPersonnel = [];
        $totalHours = 0;

        if (empty($personnel)) {
            return ['personnel' => [], 'totalHours' => 0];
        }

        // Obtener IDs únicos para consultas eficientes (fallback para datos antiguos)
        $employeeIds = array_filter(array_column($personnel, 'employee_id'));
        $positionIds = array_filter(array_column($personnel, 'position_id'));

        // Cargar empleados y posiciones en memoria solo si hay IDs
        $employees = !empty($employeeIds) ? Employee::whereIn('id', $employeeIds)->get()->keyBy('id') : collect();
        $positions = !empty($positionIds) ? Position::whereIn('id', $positionIds)->get()->keyBy('id') : collect();

        foreach ($personnel as $person) {
            $hh = $person['hh'] ?? 0;
            $totalHours += (float) $hh;

            // 1. Prioridad: employee_name del JSON (para texto libre)
            // 2. Fallback: buscar por employee_id
            $employeeName = $person['employee_name'] ?? null;
            if (!$employeeName && isset($person['employee_id'])) {
                $employee = $employees->get($person['employee_id']);
                $employeeName = $employee ? trim($employee->first_name . ' ' . $employee->last_name) : null;
            }

            // 3. Prioridad: position_name del JSON (para texto libre)
            // 4. Fallback: buscar por position_id
            $positionName = $person['position_name'] ?? null;
            if (!$positionName && isset($person['position_id'])) {
                $position = $positions->get($person['position_id']);
                $positionName = $position?->name ?? null;
            }

            $processedPersonnel[] = [
                'nombre' => $employeeName ?: 'N/A',
                'hh' => $hh,
                'cargo' => $positionName ?: 'N/A',
            ];
        }

        return [
            'personnel' => $processedPersonnel,
            'totalHours' => $totalHours,
        ];
    }

    /**
     * Crea un PDF combinando dos vistas:
     * - reports.work-report-pdf
     * - reports.photos-work-report-pdf
     *
     * @param array $data
     * @param array $customOptions
     * @return \Barryvdh\DomPDF\PDF
     */
    public function createCombinedPdf(array $data, array $customOptions = []): \Barryvdh\DomPDF\PDF
    {
        $options = array_merge(self::PDF_OPTIONS, [
            'chroot' => [
                public_path('storage'),
                public_path('images'),
                storage_path('app/public'),
            ],
        ], $customOptions);

        // Renderiza ambas vistas
        $htmlMain = view('reports.work-report-pdf', $data)->render();
        $htmlPhotos = view('reports.photos-work-report-pdf', $data)->render();

        // Combina ambas con salto de página
        $combinedHtml = $htmlMain . $htmlPhotos;

        // Genera el PDF final
        return Pdf::loadHTML($combinedHtml)
            ->setPaper('a4', 'portrait')
            ->setOptions($options);
    }

    /**
     * Genera un nombre único para el archivo
     *
     * @param WorkReport $workReport
     * @return string
     */
    public function generateFilename(WorkReport $workReport): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $reportName = Str::slug($workReport->name ?? 'reporte', '_');

        return "reporte_trabajo_{$workReport->id}_{$reportName}_{$timestamp}.pdf";
    }
}
