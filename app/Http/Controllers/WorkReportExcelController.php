<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\Employee;
use App\Models\Position;
use App\Services\CloudConvertService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class WorkReportExcelController extends Controller
{
    /**
     * Ruta de la plantilla Excel
     */
    private string $templatePath;

    protected CloudConvertService $cloudConvertService;

    public function __construct(CloudConvertService $cloudConvertService)
    {
        $this->templatePath = app_path('documents/reporte_trabajo.xlsx');
        $this->cloudConvertService = $cloudConvertService;
    }

    // ==================== MÉTODOS PÚBLICOS DE DESCARGA ====================

    /**
     * Descarga el reporte de trabajo como Excel
     */
    public function downloadExcel(int $id)
    {
        $spreadsheet = $this->generateWorkReportSpreadsheet($id);
        $filename = $this->getWorkReportFilename($id);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Descarga el reporte de trabajo como PDF
     */
    public function downloadPdf(int $id)
    {
        if (!$this->cloudConvertService->isConfigured()) {
            return CloudConvertService::errorResponse(
                'CloudConvert API key no configurada. Agrega CLOUDCONVERT_API_KEY en tu archivo .env'
            );
        }

        $spreadsheet = $this->generateWorkReportSpreadsheet($id);
        $filename = $this->getWorkReportFilename($id);

        $result = $this->cloudConvertService->spreadsheetToPdf($spreadsheet, $filename);

        if (!$result['success']) {
            return CloudConvertService::errorResponse($result['error']);
        }

        return CloudConvertService::downloadResponse($result['content'], $filename . '.pdf');
    }

    //DESCARGA DE PDFS MULTIPLES DE WORK REPORT:

    public function downloadMultiplePdf($projectId)
    {
        $workReports = WorkReport::where('project_id', $projectId)
            ->with(['employee', 'project.subClient.client', 'photos'])
            ->get();

        if ($workReports->isEmpty()) {
            return back()->with('error', 'No hay reportes disponibles para descargar');
        }

        // Generar un PDF con todas las páginas
        $html = '';

        foreach ($workReports as $report) {
            // Preparar los datos del reporte usando el método existente
            $data = $this->prepareDataForBladePdf($report);

            // Renderizar el blade para cada reporte con la vista correcta
            $html .= view('reports.report-work', $data)->render();
        }

        // Generar PDF con múltiples páginas
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'enable_remote' => true, // Permite cargar imágenes y fuentes externas
                'dpi' => 110, // Ajusta la resolución del PDF
            ]);

        Log::info('PDF múltiple de reportes de trabajo generado', [
            'project_id' => $projectId,
            'count' => $workReports->count()
        ]);

        return $pdf->download('reportes_trabajo_' . date('Y-m-d') . '.pdf');
    }
    private function parseToolsAndMaterials($toolsJson)
    {
        if (!$toolsJson)
            return [];

        $tools = is_array($toolsJson) ? $toolsJson : json_decode($toolsJson, true);
        return array_map(function ($tool) {
            return [
                'nombre' => $tool['name'] ?? $tool['nombre'] ?? '',
                'unidad' => $tool['unit'] ?? $tool['unidad'] ?? '',
                'cantidad' => $tool['quantity'] ?? $tool['cantidad'] ?? '',
            ];
        }, $tools ?? []);
    }

    private function parsePersonnel($personnelData)
    {
        if (!$personnelData)
            return [];

        return array_map(function ($person) {
            return [
                'nombre' => $person['name'] ?? $person['nombre'] ?? '',
                'hh' => $person['hours'] ?? $person['hh'] ?? 0,
                'cargo' => $person['position'] ?? $person['cargo'] ?? '',
            ];
        }, $personnelData);
    }

    private function calculateTotalHours($personnel)
    {
        return collect($personnel)->sum(function ($person) {
            return (float) ($person['hours'] ?? $person['hh'] ?? 0);
        });
    }

    private function getLogoBase64()
    {
        // Obtener el logo como base64 si existe
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        return '';
    }

    /**
     * Descarga el reporte de trabajo como PDF usando vista Blade
     *
     * Este método genera un PDF directamente desde una vista Blade,
     * procesando todos los datos del WorkReport incluyendo:
     * - Información del cliente y tienda
     * - Trabajos a realizar y realizados
     * - Herramientas y materiales
     * - Personal con sus horas y cargos
     * - Recomendaciones
     *
     * @param int $id ID del WorkReport
     * @return \Illuminate\Http\Response
     */
    public function downloadBladePdf(int $id)
    {
        // Cargar el WorkReport con todas sus relaciones necesarias
        $workReport = WorkReport::with([
            'project.subClient.client',
            'employee',
            'photos'
        ])->findOrFail($id);

        // Preparar los datos para la vista
        $data = $this->prepareDataForBladePdf($workReport);

        // Generar el PDF usando la vista Blade
        $pdf = Pdf::loadView('reports.report-work', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'dpi' => 110,
                'enable_remote' => true,
            ]);

        $filename = $this->getWorkReportFilename($id);

        Log::info('PDF de reporte de trabajo generado desde Blade', [
            'work_report_id' => $id
        ]);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Previsualiza el reporte de trabajo como PDF en el navegador
     *
     * Este método genera un PDF directamente desde una vista Blade
     * y lo muestra en el navegador (inline) sin descargarlo.
     *
     * @param int $id ID del WorkReport
     * @return \Illuminate\Http\Response
     */
    public function previewBladePdf(int $id)
    {
        // Cargar el WorkReport con todas sus relaciones necesarias
        $workReport = WorkReport::with([
            'project.subClient.client',
            'employee',
            'photos'
        ])->findOrFail($id);

        // Preparar los datos para la vista
        $data = $this->prepareDataForBladePdf($workReport);

        // Generar el PDF usando la vista Blade
        $pdf = Pdf::loadView('reports.report-work', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'dpi' => 110,
                'enable_remote' => true,
            ]);

        $filename = $this->getWorkReportFilename($id);

        Log::info('PDF de reporte de trabajo previsualizado desde Blade', [
            'work_report_id' => $id
        ]);

        // Usar stream() para mostrar en el navegador en lugar de descargar
        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Prepara todos los datos necesarios para la vista Blade del PDF
     *
     * Procesa los datos del WorkReport y los transforma en un formato
     * listo para ser consumido por la vista Blade, incluyendo:
     * - Datos básicos (fecha, horas, cliente, tienda)
     * - Herramientas y materiales (combinados y formateados)
     * - Personal con nombres completos y cargos resueltos
     * - Total de horas hombre
     *
     * @param WorkReport $workReport
     * @return array
     */
    public function prepareDataForBladePdf(WorkReport $workReport): array
    {
        // Datos básicos del reporte
        $reportDate = $workReport->report_date?->format('d/m/Y') ?? 'N/A';
        $startTime = $workReport->start_time?->format('H:i') ?? 'N/A';
        $endTime = $workReport->end_time?->format('H:i') ?? 'N/A';

        // Datos del cliente (WorkReport -> Project -> SubClient -> Client)
        $clientName = $workReport->project?->subClient?->client?->business_name ?? 'N/A';
        $documentNumber = $workReport->project?->subClient?->client?->document_number ?? 'N/A';

        // Datos de la tienda/sede (WorkReport -> Project -> SubClient)
        $storeName = $workReport->project?->subClient?->name ?? 'N/A';
        $storeAddress = $workReport->project?->subClient?->address ?? 'N/A';

        // Trabajos (limpiar HTML)
        $workToDo = $this->cleanHtmlToText($workReport->work_to_do ?? '');
        $suggestions = $this->cleanHtmlToText($workReport->suggestions ?? '');

        $conclusions = $this->cleanHtmlToText($workReport->conclusions ?? '');

        $consumedItems = [];

        // 1. Procesar Herramientas (Primero)
        $tools = $workReport->tools ?? [];
        if (!empty($tools) && is_array($tools)) {
            foreach ($tools as $tool) {
                if (empty($tool['herramienta'])) continue;

                $consumedItems[] = [
                    'description' => $tool['herramienta'],
                    'sat_line' => '',
                    'unit' => $tool['unidad'] ?? 'Und',
                    'quantity' => $tool['cantidad'] ?? 0,
                    'type' => 'tool',
                ];
            }
        }

        // 2. Procesar Materiales (Después)
        $materials = $workReport->materials ?? [];
        if (!empty($materials) && is_array($materials)) {
            $materialIds = array_column($materials, 'material_id');
            $details = \App\Models\QuoteWarehouseDetail::whereIn('id', $materialIds)
                ->with(['quoteDetail.pricelist'])
                ->get()
                ->keyBy('id');

            foreach ($materials as $m) {
                if (empty($m['material_id'])) continue;

                $detail = $details->get($m['material_id']);
                $description = $detail?->quoteDetail?->pricelist?->sat_description ?? 'Suministro';

                $consumedItems[] = [
                    'description' => $description,
                    'sat_line' => $m['sat_line'] ?? ($detail?->quoteDetail?->pricelist?->sat_line ?? ''),
                    'unit' => $m['unit_name'] ?? '',
                    'quantity' => $m['used_quantity'] ?? 0,
                    'type' => 'material',
                ];
            }
        }

        // Procesar personal con nombres y cargos
        $personnelData = $this->processPersonnelForPdf($workReport->personnel ?? []);

        // Cargar logo como base64 para el PDF
        $logoPath = public_path('images/logo2.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        return [
            'workReport' => $workReport,
            'reportId' => $workReport->id,
            'reportDate' => $reportDate,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'clientName' => $clientName,
            'documentNumber' => $documentNumber,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            //'workDone' => $workDone ?: 'N/A',
            'workToDo' => $workToDo ?: 'N/A',
            'conclusions' => $conclusions ?: 'N/A',
            'suggestions' => $suggestions ?: 'N/A',
            'consumedItems' => $consumedItems,
            'personnel' => $personnelData['personnel'],
            'totalHours' => $personnelData['totalHours'],
            'logoBase64' => $logoBase64,
        ];
    }

    /*
    public function processToolsAndMaterials(array $tools, array $materials): array
    {
        $combined = [];

        // Agregar herramientas
        foreach ($tools as $tool) {
            $combined[] = [
                'nombre' => $tool['herramienta'] ?? '',
                'unidad' => $tool['unidad'] ?? '',
                'cantidad' => $tool['cantidad'] ?? '',
            ];
        }

        // Agregar materiales
        foreach ($materials as $material) {
            $combined[] = [
                'nombre' => $material['material'] ?? '',
                'unidad' => $material['unidad'] ?? '',
                'cantidad' => $material['cantidad'] ?? '',
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
    public function processPersonnelForPdf(array $personnel): array
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
     * Genera el nombre del archivo para el reporte
     */
    private function getWorkReportFilename(int $id): string
    {
        $workReport = WorkReport::with('project')->find($id);
        $projectName = $workReport?->project?->name ?? 'sin_proyecto';
        $date = $workReport?->report_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        // Limpiar caracteres especiales del nombre
        $projectName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $projectName);

        return "reporte_trabajo_{$projectName}_{$date}";
    }

    /**
     * Genera el Spreadsheet del reporte de trabajo
     */
    private function generateWorkReportSpreadsheet(int $id): Spreadsheet
    {
        // Cargar el WorkReport con sus relaciones
        $workReport = WorkReport::with([
            'project.subClient.client',
            'employee',
            'photos'
        ])->findOrFail($id);

        // Verificar que la plantilla existe
        if (!file_exists($this->templatePath)) {
            abort(404, 'Plantilla no encontrada: ' . $this->templatePath);
        }

        // Cargar la plantilla Excel
        $spreadsheet = IOFactory::load($this->templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Llenar los datos en las celdas correspondientes
        $this->fillReportData($sheet, $workReport);

        Log::info('Spreadsheet de reporte de trabajo generado', [
            'work_report_id' => $id
        ]);

        return $spreadsheet;
    }

    // ==================== MÉTODO LEGACY (para compatibilidad) ====================

    /**
     * @deprecated Usa downloadExcel() en su lugar
     */
    public function generateReport(int $workReport, Request $request)
    {
        return $this->downloadExcel($workReport);
    }

    /**
     * Llena los datos del reporte en la hoja de cálculo
     *
     * Mapeo de celdas:
     * - K4:  Fecha del reporte (concatenado con "FECHA: ")
     * - M6:  Hora de inicio del trabajo
     * - M8:  Hora de finalización del trabajo
     * - C11: Nombre del cliente (business_name)
     * - J11: RUC del cliente (document_number)
     * - C13: Nombre de la tienda/sede (SubClient name)
     * - J13: Dirección de la tienda/sede (SubClient address)
     * - B24: Trabajos a realizar (work_to_do)
     * - B56: Recomendaciones (suggestions)
     *
     * Tabla de Materiales/Herramientas (desde fila 31):
     * - B30: Encabezado "Materiales/Herramientas"
     * - J30: Encabezado "Unidad"
     * - L30: Encabezado "Cantidad"
     * - B31+: Datos de tools y materials JSON
     *
     * Tabla de Personal (desde fila 45):
     * - B44: Encabezado "Personal que realizó el trabajo"
     * - J44: Encabezado "H.H"
     * - L44: Encabezado "Cargo"
     * - B45+: Datos de personnel JSON (employee_id, hh, position_id)
     * - J53: Total de horas hombre
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param WorkReport $workReport
     * @return void
     */
    private function fillReportData($sheet, WorkReport $workReport): void
    {
        // K4 - Fecha del reporte (concatenado con "FECHA: ")
        $reportDate = $workReport->report_date?->format('d/m/Y') ?? 'N/A';
        $sheet->setCellValue('K4', 'FECHA: ' . $reportDate);

        // M6 - Hora de inicio del trabajo
        $startTime = $workReport->start_time?->format('H:i') ?? 'N/A';
        $sheet->setCellValue('M6', $startTime);

        // M8 - Hora de finalización del trabajo
        $endTime = $workReport->end_time?->format('H:i') ?? 'N/A';
        $sheet->setCellValue('M8', $endTime);

        // C11 - Nombre del cliente (business_name)
        // Ruta: WorkReport -> Project -> SubClient -> Client -> business_name
        $clientName = $workReport->project?->subClient?->client?->business_name ?? 'N/A';
        $sheet->setCellValue('C11', $clientName);

        // J11 - Número de RUC del cliente (document_number)
        // Ruta: WorkReport -> Project -> SubClient -> Client -> document_number
        $documentNumber = $workReport->project?->subClient?->client?->document_number ?? 'N/A';
        $sheet->setCellValue('J11', $documentNumber);

        // C13 - Nombre de tienda/sede (name)
        // Ruta: WorkReport -> Project -> SubClient -> name
        $storeName = $workReport->project?->subClient?->name ?? 'N/A';
        $sheet->setCellValue('C13', $storeName);

        // J13 - Dirección de tienda/sede (address)
        // Ruta: WorkReport -> Project -> SubClient -> address
        $storeAddress = $workReport->project?->subClient?->address ?? 'N/A';
        $sheet->setCellValue('J13', $storeAddress);

        // B24 - Trabajos a realizar (work_to_do)
        // Se limpia el HTML del RichEditor y se convierte a texto plano
        $workToDo = $this->cleanHtmlToText($workReport->work_to_do ?? '');
        $sheet->setCellValue('B18', $workToDo ?: 'N/A');

        $conclusions = $this->cleanHtmlToText($workReport->conclusions ?? '');
        $sheet->setCellValue('B25', $conclusions ?: 'N/A');

        $id = $this->cleanHtmlToText($workReport->id ?? '');
        $sheet->setCellValue('L2', 'N° ' . str_pad($workReport->id, 6, '0', STR_PAD_LEFT));


        // Tabla de Materiales/Herramientas
        // Encabezados en fila 30, datos desde fila 31
        // Primero: tools JSON [{"herramienta":"taladro","unidad":"unidad","cantidad":"2"}]
        // Después: materials JSON [{"material":"Cemento","unidad":"sacos","cantidad":"2"}]
        $lastRow = $this->fillToolsAndMaterialsTable($sheet, $workReport->tools ?? [], $workReport->materials ?? [], 31);

        // Tabla de Personal
        // Encabezados en fila 44, datos desde fila 45
        // JSON: [{"employee_id":3,"hh":"2","position_id":"3"}]
        $this->fillPersonnelTable($sheet, $workReport->personnel ?? [], 45);

        // B56 - Recomendaciones (suggestions)
        // Se limpia el HTML del RichEditor y se convierte a texto plano
        $suggestions = $this->cleanHtmlToText($workReport->suggestions ?? '');
        $sheet->setCellValue('B56', $suggestions ?: 'N/A');
    }

    /**
     * Llena la tabla de herramientas y materiales en el Excel
     *
     * Primero recorre el array de tools (herramientas) y luego
     * continúa con el array de materials (materiales) en las siguientes filas.
     *
     * Columnas:
     * - B: Materiales/Herramientas (herramienta o material)
     * - J: Unidad (unidad)
     * - L: Cantidad (cantidad)
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $tools Array de herramientas del JSON [{"herramienta":"...","unidad":"...","cantidad":"..."}]
     * @param array $materials Array de materiales del JSON [{"material":"...","unidad":"...","cantidad":"..."}]
     * @param int $startRow Fila inicial para los datos (después de encabezados)
     * @return int Última fila utilizada
     */
    private function fillToolsAndMaterialsTable($sheet, array $tools, array $materials, int $startRow): int
    {
        $currentRow = $startRow;

        // Primero: Herramientas (tools)
        // JSON: [{"herramienta":"taladro","unidad":"unidad","cantidad":"2"}]
        foreach ($tools as $tool) {
            // B - Herramienta
            $sheet->setCellValue('B' . $currentRow, $tool['herramienta'] ?? '');

            // J - Unidad
            $sheet->setCellValue('J' . $currentRow, $tool['unidad'] ?? '');

            // L - Cantidad
            $sheet->setCellValue('L' . $currentRow, $tool['cantidad'] ?? '');

            $currentRow++;
        }

        // Después: Materiales (materials)
        // JSON: [{"material":"Cemento","unidad":"sacos","cantidad":"2"}]
        foreach ($materials as $material) {
            // B - Material
            $sheet->setCellValue('B' . $currentRow, $material['material'] ?? '');

            // J - Unidad
            $sheet->setCellValue('J' . $currentRow, $material['unidad'] ?? '');

            // L - Cantidad
            $sheet->setCellValue('L' . $currentRow, $material['cantidad'] ?? '');

            $currentRow++;
        }

        return $currentRow;
    }

    /**
     * Llena la tabla de personal en el Excel
     *
     * Columnas:
     * - B44: Personal que realizó el trabajo (nombre completo del empleado)
     * - J44: H.H (horas hombre)
     * - L44: Cargo (nombre del cargo/posición)
     * - J53: Total de horas hombre
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $personnel Array de personal del JSON [{"employee_id":3,"hh":"2","position_id":"3"}]
     * @param int $startRow Fila inicial para los datos (después de encabezados)
     * @return int Última fila utilizada
     */
    private function fillPersonnelTable($sheet, array $personnel, int $startRow): int
    {
        $currentRow = $startRow;
        $totalHours = 0;

        // Obtener todos los IDs de empleados y posiciones para fallbacks
        $employeeIds = array_filter(array_column($personnel, 'employee_id'));
        $positionIds = array_filter(array_column($personnel, 'position_id'));

        // Cargar empleados y posiciones en memoria solo si hay IDs
        $employees = !empty($employeeIds) ? Employee::whereIn('id', $employeeIds)->get()->keyBy('id') : collect();
        $positions = !empty($positionIds) ? Position::whereIn('id', $positionIds)->get()->keyBy('id') : collect();

        foreach ($personnel as $person) {
            $hh = $person['hh'] ?? 0;
            $totalHours += (float) $hh;

            // 1. Prioridad: employee_name del JSON
            $employeeName = $person['employee_name'] ?? null;
            if (!$employeeName && isset($person['employee_id'])) {
                $employee = $employees->get($person['employee_id']);
                $employeeName = $employee ? trim($employee->first_name . ' ' . $employee->last_name) : null;
            }

            // 2. Prioridad: position_name del JSON
            $positionName = $person['position_name'] ?? null;
            if (!$positionName && isset($person['position_id'])) {
                $position = $positions->get($person['position_id']);
                $positionName = $position?->name ?? null;
            }

            // B - Personal que realizó el trabajo (nombre completo)
            $sheet->setCellValue('B' . $currentRow, $employeeName ?: 'N/A');

            // J - H.H (horas hombre)
            $sheet->setCellValue('J' . $currentRow, $hh);

            // L - Cargo
            $sheet->setCellValue('L' . $currentRow, $positionName ?: 'N/A');

            $currentRow++;
        }

        // J53 - Total de horas hombre
        $sheet->setCellValue('J53', $totalHours);

        return $currentRow;
    }

    /**
     * Limpia el contenido HTML y lo convierte a texto plano
     *
     * Conversiones realizadas:
     * - <br>, <br/>, </p> → salto de línea
     * - <li> → "• " (bullet point)
     * - </li> → salto de línea
     * - <h2>, <h3> → salto de línea + texto en mayúsculas
     * - Demás etiquetas HTML → eliminadas
     * - Entidades HTML → decodificadas
     *
     * @param string $html Contenido HTML del RichEditor
     * @return string Texto plano limpio
     */
    private function cleanHtmlToText(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Reemplazar saltos de línea HTML por marcadores temporales
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/p>/i', "\n", $text);
        $text = preg_replace('/<\/div>/i', "\n", $text);

        // Reemplazar listas
        $text = preg_replace('/<li>/i', '• ', $text);
        $text = preg_replace('/<\/li>/i', "\n", $text);
        $text = preg_replace('/<\/?[uo]l>/i', "\n", $text);

        // Reemplazar encabezados (agregar salto de línea antes)
        $text = preg_replace('/<h[2-6][^>]*>/i', "\n", $text);
        $text = preg_replace('/<\/h[2-6]>/i', "\n", $text);

        // Eliminar todas las demás etiquetas HTML
        $text = strip_tags($text);

        // Decodificar entidades HTML (&nbsp;, &amp;, etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Limpiar espacios múltiples y saltos de línea excesivos
        $text = preg_replace('/[ \t]+/', ' ', $text); // Múltiples espacios → uno solo
        $text = preg_replace('/\n\s*\n\s*\n/', "\n\n", $text); // Máximo 2 saltos de línea seguidos

        // Eliminar espacios al inicio y final de cada línea
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);

        // Eliminar espacios al inicio y final del texto completo
        return trim($text);
    }
}
