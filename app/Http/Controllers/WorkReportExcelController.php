<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkReportExcelController extends Controller
{
    /**
     * Ruta de la plantilla Excel
     */
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = app_path('documents/reporte_trabajo.xlsx');
    }

    /**
     * Genera un reporte de trabajo en formato Excel
     *
     * @param int $workReport
     * @param Request $request
     * @return BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function generateReport(int $workReport, Request $request)
    {
        try {
            // Cargar el WorkReport con sus relaciones
            // El cliente se obtiene a través de: Project -> SubClient -> Client
            $workReportModel = WorkReport::with([
                'project.subClient.client',
                'employee',
                'photos'
            ])->findOrFail($workReport);

            // Cargar la plantilla Excel
            $spreadsheet = IOFactory::load($this->templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Llenar los datos en las celdas correspondientes
            $this->fillReportData($sheet, $workReportModel);

            // Generar nombre del archivo
            $filename = $this->generateFilename($workReportModel);

            // Crear archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'work_report_') . '.xlsx';
            
            // Guardar el archivo
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($tempFile);

            Log::info('Excel de reporte de trabajo generado', [
                'work_report_id' => $workReport,
                'filename' => $filename
            ]);

            // Retornar el archivo para descarga
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error generando Excel de reporte de trabajo', [
                'work_report_id' => $workReport,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar el reporte Excel',
                'message' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
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
    }

    /**
     * Genera el nombre del archivo Excel
     *
     * @param WorkReport $workReport
     * @return string
     */
    private function generateFilename(WorkReport $workReport): string
    {
        $projectName = $workReport->project?->name ?? 'sin_proyecto';
        $date = $workReport->report_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        
        // Limpiar caracteres especiales del nombre
        $projectName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $projectName);
        
        return "reporte_trabajo_{$projectName}_{$date}.xlsx";
    }
}
