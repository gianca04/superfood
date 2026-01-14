<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Services\WorkReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controlador para generar Informes de Evidencias (PDF con fotos)
 *
 * Este controlador genera un PDF que incluye:
 * - Información del reporte de trabajo
 * - Fotografías/evidencias asociadas
 */
class EvidenceReportController extends Controller
{
    public function __construct(
        private WorkReportPdfService $pdfService
    ) {}

    /**
     * Genera un informe de evidencias en formato PDF
     *
     * @param int $workReportId
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function generateReport(int $workReportId, Request $request)
    {
        try {
            // Validar parámetros de la request
            $request->validate([
                'inline' => 'boolean',
                'async' => 'boolean',
                'email' => 'email|nullable'
            ]);

            // Si se solicita generación asíncrona
            if ($request->boolean('async')) {
                return $this->generateAsync($workReportId, $request);
            }

            // Generación síncrona
            return $this->generateSync($workReportId, $request);
        } catch (\Exception $e) {
            Log::error('Error generando Informe de Evidencias', [
                'work_report_id' => $workReportId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar el Informe de Evidencias',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera PDF de forma síncrona (siempre en tiempo real)
     */
    private function generateSync(int $workReportId, Request $request): Response
    {
        // Generar PDF en tiempo real
        $pdf = $this->pdfService->generateSync($workReportId);
        $workReport = $this->pdfService->getWorkReportWithRelations($workReportId);
        $filename = $this->pdfService->generateFilename($workReport);

        // Determinar disposición
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        Log::info('Informe de Evidencias generado sincrónicamente', [
            'work_report_id' => $workReportId,
            'filename' => $filename,
            'disposition' => $disposition
        ]);

        if ($disposition === 'inline') {
            return $pdf->stream($filename);
        } else {
            return $pdf->download($filename);
        }
    }

    /**
     * Inicia generación asíncrona
     */
    private function generateAsync(int $workReportId, Request $request): JsonResponse
    {
        $userEmail = $request->input('email');
        $shouldEmail = !empty($userEmail);

        $this->pdfService->generateAsync($workReportId, $userEmail, $shouldEmail);

        Log::info('Generación asíncrona de Informe de Evidencias iniciada', [
            'work_report_id' => $workReportId,
            'should_email' => $shouldEmail,
            'email' => $userEmail
        ]);

        return response()->json([
            'message' => 'Generación de Informe de Evidencias iniciada',
            'work_report_id' => $workReportId,
            'async' => true,
            'email_notification' => $shouldEmail
        ], 202);
    }

    /**
     * Fuerza la regeneración de un PDF
     *
     * @param int $workReportId
     * @return JsonResponse
     */
    public function regeneratePdf(int $workReportId): JsonResponse
    {
        try {
            // Generar PDF en tiempo real
            $pdf = $this->pdfService->generateSync($workReportId);
            $workReport = $this->pdfService->getWorkReportWithRelations($workReportId);
            $filename = $this->pdfService->generateFilename($workReport);

            return response()->json([
                'message' => 'Informe de Evidencias generado exitosamente',
                'work_report_id' => $workReportId,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error generando Informe de Evidencias', [
                'work_report_id' => $workReportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al generar el Informe de Evidencias',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
