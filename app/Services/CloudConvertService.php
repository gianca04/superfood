<?php

namespace App\Services;

use CloudConvert\CloudConvert;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;
use CloudConvert\Exceptions\HttpClientException;
use CloudConvert\Exceptions\HttpServerException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class CloudConvertService
{
    private CloudConvert $cloudConvert;
    private bool $isConfigured = false;

    public function __construct()
    {
        $apiKey = config('cloudconvert.api_key');
        
        if (!empty($apiKey)) {
            $this->cloudConvert = new CloudConvert([
                'api_key' => $apiKey,
                'sandbox' => config('cloudconvert.sandbox', false)
            ]);
            $this->isConfigured = true;
        }
    }

    /**
     * Verifica si el servicio está configurado correctamente
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Convierte un Spreadsheet a PDF y retorna el contenido binario
     *
     * @param Spreadsheet $spreadsheet El spreadsheet a convertir
     * @param string|null $originalFilename Nombre original para el archivo temporal
     * @return array ['success' => bool, 'content' => string|null, 'error' => string|null]
     */
    public function spreadsheetToPdf(Spreadsheet $spreadsheet, ?string $originalFilename = null): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'CloudConvert API key no configurada. Agrega CLOUDCONVERT_API_KEY en tu archivo .env'
            ];
        }

        $tempExcelPath = $this->saveTempSpreadsheet($spreadsheet, $originalFilename);

        try {
            $pdfContent = $this->convertFileToPdf($tempExcelPath, 'xlsx');
            $this->cleanupTempFile($tempExcelPath);

            return [
                'success' => true,
                'content' => $pdfContent,
                'error' => null
            ];
        } catch (\Exception $e) {
            $this->cleanupTempFile($tempExcelPath);
            Log::error('CloudConvert Error: ' . $e->getMessage());

            return [
                'success' => false,
                'content' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convierte un archivo Excel (ruta) a PDF y retorna el contenido binario
     *
     * @param string $excelPath Ruta al archivo Excel
     * @return array ['success' => bool, 'content' => string|null, 'error' => string|null]
     */
    public function excelFileToPdf(string $excelPath): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'CloudConvert API key no configurada. Agrega CLOUDCONVERT_API_KEY en tu archivo .env'
            ];
        }

        if (!file_exists($excelPath)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'El archivo no existe: ' . $excelPath
            ];
        }

        try {
            $extension = pathinfo($excelPath, PATHINFO_EXTENSION) ?: 'xlsx';
            $pdfContent = $this->convertFileToPdf($excelPath, $extension);

            return [
                'success' => true,
                'content' => $pdfContent,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('CloudConvert Error: ' . $e->getMessage());

            return [
                'success' => false,
                'content' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convierte cualquier archivo a PDF usando CloudConvert
     *
     * @param string $filePath Ruta al archivo a convertir
     * @param string $inputFormat Formato de entrada (xlsx, docx, pptx, etc.)
     * @return string Contenido binario del PDF
     * @throws \Exception
     */
    public function convertFileToPdf(string $filePath, string $inputFormat): string
    {
        if (!$this->isConfigured) {
            throw new \Exception('CloudConvert API key no configurada');
        }

        // Crear el Job de CloudConvert
        $job = (new Job())
            ->addTask(new Task('import/upload', 'upload-task'))
            ->addTask(
                (new Task('convert', 'convert-task'))
                    ->set('input', 'upload-task')
                    ->set('input_format', $inputFormat)
                    ->set('output_format', 'pdf')
                    ->set('engine', 'office')
            )
            ->addTask(
                (new Task('export/url', 'export-task'))
                    ->set('input', 'convert-task')
            );

        $job = $this->cloudConvert->jobs()->create($job);

        // Subir el archivo - Buscar la tarea de upload
        /** @var \CloudConvert\Models\TaskCollection|null $tasks */
        $tasks = $job->getTasks();
        
        if ($tasks === null) {
            throw new \Exception('No se pudieron obtener las tareas del job');
        }
        
        if ($tasks->count() === 0) {
            throw new \Exception('El job no tiene tareas configuradas');
        }

        /** @var \CloudConvert\Models\TaskCollection $uploadTasks */
        $uploadTasks = $tasks->whereName('upload-task');
        
        if ($uploadTasks === null || $uploadTasks->count() === 0) {
            throw new \Exception('No se encontró la tarea de upload');
        }
        
        /** @var \CloudConvert\Models\Task $uploadTask */
        $uploadTask = $uploadTasks[0];
        $filename = basename($filePath);
        
        $this->cloudConvert->tasks()->upload($uploadTask, fopen($filePath, 'r'), $filename);

        // Esperar a que termine la conversión
        $this->cloudConvert->jobs()->wait($job);

        // Obtener el link de descarga
        $job = $this->cloudConvert->jobs()->get($job->getId());
        $exportUrls = $job->getExportUrls();

        if (empty($exportUrls)) {
            throw new \Exception('No se pudo obtener el archivo PDF convertido');
        }

        $downloadUrl = $exportUrls[0]->url;

        // Descargar el contenido del PDF
        $pdfContent = file_get_contents($downloadUrl, false, stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]));

        if ($pdfContent === false) {
            throw new \Exception('Error al descargar el PDF convertido');
        }

        return $pdfContent;
    }

    /**
     * Convierte un archivo Word (DOCX) a PDF
     *
     * @param string $docxPath Ruta al archivo Word
     * @return array ['success' => bool, 'content' => string|null, 'error' => string|null]
     */
    public function wordToPdf(string $docxPath): array
    {
        if (!file_exists($docxPath)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'El archivo no existe: ' . $docxPath
            ];
        }

        try {
            $pdfContent = $this->convertFileToPdf($docxPath, 'docx');

            return [
                'success' => true,
                'content' => $pdfContent,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('CloudConvert Word to PDF Error: ' . $e->getMessage());

            return [
                'success' => false,
                'content' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convierte un archivo PowerPoint (PPTX) a PDF
     *
     * @param string $pptxPath Ruta al archivo PowerPoint
     * @return array ['success' => bool, 'content' => string|null, 'error' => string|null]
     */
    public function powerpointToPdf(string $pptxPath): array
    {
        if (!file_exists($pptxPath)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'El archivo no existe: ' . $pptxPath
            ];
        }

        try {
            $pdfContent = $this->convertFileToPdf($pptxPath, 'pptx');

            return [
                'success' => true,
                'content' => $pdfContent,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('CloudConvert PowerPoint to PDF Error: ' . $e->getMessage());

            return [
                'success' => false,
                'content' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Guarda un Spreadsheet como archivo temporal
     */
    private function saveTempSpreadsheet(Spreadsheet $spreadsheet, ?string $filename = null): string
    {
        $filename = $filename ?? 'temp_' . uniqid() . '_' . time();
        $tempPath = storage_path('app/' . $filename . '.xlsx');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Limpia archivos temporales
     */
    private function cleanupTempFile(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Genera una respuesta de descarga para el contenido PDF
     *
     * @param string $pdfContent Contenido binario del PDF
     * @param string $filename Nombre del archivo para la descarga
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function downloadResponse(string $pdfContent, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Genera una respuesta de error JSON
     */
    public static function errorResponse(string $error, int $statusCode = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $error
        ], $statusCode);
    }
}
