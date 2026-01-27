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
use Illuminate\Support\Facades\Cache;

class CloudConvertService
{
    private ?CloudConvert $cloudConvert = null;
    private bool $isConfigured = false;
    private array $apiKeys = [];
    private int $currentKeyIndex = 0;

    /**
     * Cache key para guardar el índice de la última API key que funcionó
     */
    private const CACHE_KEY_INDEX = 'cloudconvert_current_key_index';

    /**
     * Tiempo de caché para recordar la última API key usada (24 horas)
     */
    private const CACHE_TTL = 86400;

    public function __construct()
    {
        // Cargar todas las API keys disponibles
        //$this->loadApiKeys();

        // Recuperar el índice de la última API key que funcionó
        $this->currentKeyIndex = Cache::get(self::CACHE_KEY_INDEX, 0);

        // Validar que el índice esté dentro del rango
        if ($this->currentKeyIndex >= count($this->apiKeys)) {
            $this->currentKeyIndex = 0;
        }

        // Inicializar con la API key actual
        $this->initializeClient();
    }

    /*
    Carga todas las API keys disponibles desde la configuración
    private function loadApiKeys(): void
    {
        $this->apiKeys = [];
        
        // Cargar la API key principal
        $mainKey = config('cloudconvert.api_key');
        if (!empty($mainKey)) {
            $this->apiKeys[] = $mainKey;
        }
        
        // Cargar API keys adicionales (CLOUDCONVERT_API_KEY2, CLOUDCONVERT_API_KEY3, etc.)
        for ($i = 2; $i <= 10; $i++) {
            $key = env("CLOUDCONVERT_API_KEY{$i}");
            if (!empty($key)) {
                $this->apiKeys[] = $key;
            }
        }
        
        Log::info('CloudConvert: ' . count($this->apiKeys) . ' API keys cargadas');
    }
     */

    /**
     * Inicializa el cliente con la API key actual
     */
    private function initializeClient(): void
    {
        if (empty($this->apiKeys)) {
            $this->isConfigured = false;
            return;
        }

        $apiKey = $this->apiKeys[$this->currentKeyIndex] ?? null;

        if (!empty($apiKey)) {
            $this->cloudConvert = new CloudConvert([
                'api_key' => $apiKey,
                'sandbox' => config('cloudconvert.sandbox', false)
            ]);
            $this->isConfigured = true;

            Log::debug('CloudConvert: Usando API key #' . ($this->currentKeyIndex + 1));
        } else {
            $this->isConfigured = false;
        }
    }

    /**
     * Cambia a la siguiente API key disponible
     * 
     * @return bool True si se pudo cambiar a otra key, False si no hay más disponibles
     */
    private function switchToNextApiKey(): bool
    {
        $totalKeys = count($this->apiKeys);

        if ($totalKeys <= 1) {
            return false;
        }

        // Intentar con la siguiente key
        $nextIndex = ($this->currentKeyIndex + 1) % $totalKeys;

        // Si volvimos al inicio, significa que ya probamos todas
        if ($nextIndex === 0 && $this->currentKeyIndex !== 0) {
            Log::warning('CloudConvert: Todas las API keys han sido probadas sin éxito');
            return false;
        }

        $this->currentKeyIndex = $nextIndex;
        $this->initializeClient();

        // Guardar el nuevo índice en caché
        Cache::put(self::CACHE_KEY_INDEX, $this->currentKeyIndex, self::CACHE_TTL);

        Log::info('CloudConvert: Cambiando a API key #' . ($this->currentKeyIndex + 1));

        return true;
    }

    /**
     * Verifica si el error indica que se agotaron los créditos
     */
    private function isCreditsExhaustedError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        Log::debug('CloudConvert: Verificando error - ' . $message);

        // Patrones comunes de errores por créditos agotados
        $patterns = [
            'credits',
            'credit',
            'run out of',
            'out of conversion',
            'quota',
            'limit exceeded',
            'rate limit',
            'insufficient',
            'payment required',
            '402',
            '429',
            'too many requests',
            'exceeded',
            'exhausted',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                Log::info('CloudConvert: Detectado error de créditos - patrón: ' . $pattern);
                return true;
            }
        }

        // También verificar el código de error HTTP
        if ($e instanceof HttpClientException) {
            $code = $e->getCode();
            if (in_array($code, [402, 429, 503])) {
                Log::info('CloudConvert: Detectado error de créditos - código HTTP: ' . $code);
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el servicio está configurado correctamente
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Obtiene información sobre las API keys configuradas
     */
    public function getApiKeysInfo(): array
    {
        return [
            'total_keys' => count($this->apiKeys),
            'current_key_index' => $this->currentKeyIndex + 1,
            'is_configured' => $this->isConfigured,
        ];
    }

    /**
     * Convierte un Spreadsheet a PDF y retorna el contenido binario
     * Con soporte para múltiples API keys
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
        $startingKeyIndex = $this->currentKeyIndex;
        $lastError = null;

        // Intentar con todas las API keys disponibles
        do {
            try {
                $pdfContent = $this->convertFileToPdf($tempExcelPath, 'xlsx');
                $this->cleanupTempFile($tempExcelPath);

                return [
                    'success' => true,
                    'content' => $pdfContent,
                    'error' => null
                ];
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning('CloudConvert Error con API key #' . ($this->currentKeyIndex + 1) . ': ' . $e->getMessage());

                // Si es un error de créditos, intentar con la siguiente key
                if ($this->isCreditsExhaustedError($e)) {
                    if (!$this->switchToNextApiKey()) {
                        break; // No hay más keys disponibles
                    }
                    // Si volvimos a la key inicial, salir del loop
                    if ($this->currentKeyIndex === $startingKeyIndex) {
                        break;
                    }
                } else {
                    // Error no relacionado con créditos, no reintentar
                    break;
                }
            }
        } while (true);

        $this->cleanupTempFile($tempExcelPath);

        return [
            'success' => false,
            'content' => null,
            'error' => $lastError ? $lastError->getMessage() : 'Error desconocido'
        ];
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

        $startingKeyIndex = $this->currentKeyIndex;
        $lastError = null;

        // Intentar con todas las API keys disponibles
        do {
            try {
                $extension = pathinfo($excelPath, PATHINFO_EXTENSION) ?: 'xlsx';
                $pdfContent = $this->convertFileToPdf($excelPath, $extension);

                return [
                    'success' => true,
                    'content' => $pdfContent,
                    'error' => null
                ];
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning('CloudConvert Error con API key #' . ($this->currentKeyIndex + 1) . ': ' . $e->getMessage());

                if ($this->isCreditsExhaustedError($e)) {
                    if (!$this->switchToNextApiKey()) {
                        break;
                    }
                    if ($this->currentKeyIndex === $startingKeyIndex) {
                        break;
                    }
                } else {
                    break;
                }
            }
        } while (true);

        return [
            'success' => false,
            'content' => null,
            'error' => $lastError ? $lastError->getMessage() : 'Error desconocido'
        ];
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

        try {
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
        } catch (HttpClientException $e) {
            // Capturar errores HTTP del cliente (4xx)
            $errorMessage = $e->getMessage();

            // Intentar extraer el mensaje del response body si existe
            try {
                $response = $e->getResponse();
                if ($response) {
                    $body = json_decode($response->getBody()->getContents(), true);
                    if (isset($body['message'])) {
                        $errorMessage = $body['message'];
                    }
                }
            } catch (\Exception $parseError) {
                // Ignorar errores al parsear
            }

            Log::error('CloudConvert HttpClientException: ' . $errorMessage);
            throw new \Exception($errorMessage, $e->getCode(), $e);
        } catch (HttpServerException $e) {
            // Capturar errores HTTP del servidor (5xx)
            Log::error('CloudConvert HttpServerException: ' . $e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Capturar errores de Guzzle directamente
            $errorMessage = $e->getMessage();

            try {
                $response = $e->getResponse();
                if ($response) {
                    $body = json_decode($response->getBody()->getContents(), true);
                    if (isset($body['message'])) {
                        $errorMessage = $body['message'];
                    }
                }
            } catch (\Exception $parseError) {
                // Ignorar errores al parsear
            }

            Log::error('CloudConvert GuzzleException: ' . $errorMessage);
            throw new \Exception($errorMessage, $e->getCode(), $e);
        }
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
