<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Services\CloudConvertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ExcelExportController extends Controller
{
    protected CloudConvertService $cloudConvertService;

    public function __construct(CloudConvertService $cloudConvertService)
    {
        $this->cloudConvertService = $cloudConvertService;
    }

    public function downloadAsPdf(Spreadsheet $spreadsheet, string $filename)
    {
        if (!$this->cloudConvertService->isConfigured()) {
            return CloudConvertService::errorResponse(
                'CloudConvert API key no configurada. Agrega CLOUDCONVERT_API_KEY en tu archivo .env'
            );
        }

        $result = $this->cloudConvertService->spreadsheetToPdf($spreadsheet, $filename);

        if (!$result['success']) {
            return CloudConvertService::errorResponse($result['error']);
        }

        return CloudConvertService::downloadResponse($result['content'], $filename . '.pdf');
    }

    public function downloadAsExcel(Spreadsheet $spreadsheet, string $filename)
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    
    /**
     * Descarga el Acta de Conformidad como PDF usando CloudConvert
     */
    // ==================== ACTA DE CONFORMIDAD ====================

    public function downloadActaPdf($id)
    {
        $spreadsheet = $this->generateActaSpreadsheet($id);
        $filename = $this->getActaFilename($id);
        
        return $this->downloadAsPdf($spreadsheet, $filename);
    }
    
    public function downloadActaExcel($id)
    {
        $spreadsheet = $this->generateActaSpreadsheet($id);
        $filename = $this->getActaFilename($id);
        
        return $this->downloadAsExcel($spreadsheet, $filename);
    }
    private function getActaFilename($id): string
    {
        $compliance = Compliance::with('project.quote')->find($id);
        $quote = $compliance?->project?->quote;
        return 'Acta_Conformidad_' . ($quote?->correlative ?? $compliance->id);
    }

    private function generateActaSpreadsheet($id)
    {
        // 1️⃣ Obtener datos
        $compliance = Compliance::with([
            'project.subClient.client',
            'project.quote'
        ])->findOrFail($id);

        $project = $compliance->project;
        $subClient = $project?->subClient;
        $client = $subClient?->client;
        $quote = $project?->quote;

        $user = Auth::user();
        $employee = $user?->employee;

        // 2️⃣ Cargar plantilla
        $templatePath = app_path('Documents/formatoActaConformidad.xlsx');
        
        if (!file_exists($templatePath)) {
            abort(404, 'Plantilla no encontrada');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 3️⃣ Llenar datos
        $sheet->setCellValue('L2', 'N° ' . str_pad($compliance->id, 6, '0', STR_PAD_LEFT));
        $sheet->setCellValue('E7', $client?->business_name ?? '');
        $sheet->setCellValueExplicit('J7', $client?->document_number ?? '', DataType::TYPE_STRING);
        $sheet->setCellValue('E9', $subClient?->name ?? '');
        $sheet->setCellValue('J9', $subClient?->address ?? '');
        $sheet->setCellValue('B12', $quote?->correlative ?? 'OT-' . ($project?->id ?? ''));
        $sheet->setCellValue('E12', $quote?->project_description ?? $project?->name ?? '');
        $sheet->setCellValue('E15', $project?->start_date?->format('d/m/Y') ?? '');
        $sheet->setCellValue('K15', $project?->end_date?->format('d/m/Y') ?? '');

        // 4️⃣ Activos
        $assets = $compliance->assets ?? [];
        $assetsMap = [
            'tablero_autosoportado' => ['row' => 24, 'name' => 'Tablero Autosoportado'],
            'tablero_adosados' => ['row' => 25, 'name' => 'Tablero Adosados'],
            'banco_condensadores' => ['row' => 26, 'name' => 'Banco de Condensadores'],
            'pozos_baja_tension' => ['row' => 27, 'name' => 'Pozos a Tierra Baja Tensión'],
            'pozos_media_tension' => ['row' => 28, 'name' => 'Pozos a Tierra Media Tensión'],
        ];

        foreach ($assetsMap as $key => $config) {
            $row = $config['row'];
            $name = $config['name'];
            $assetData = $assets[$key] ?? [];
            $isSelected = $assetData['selected'] ?? false;
            
            if ($isSelected) {
                $sheet->setCellValue("C{$row}", "{$name}           ( X )");
                $sheet->setCellValue("I{$row}", $assetData['quantity'] ?? '');
                $sheet->setCellValue("K{$row}", $assetData['comments'] ?? '');
            } else {
                $sheet->setCellValue("C{$row}", "{$name}           (    )");
            }
        }

        // 5️⃣ Observaciones
        $htmlObservations = $compliance->maintenance_observations ?? '';
        if (!empty($htmlObservations)) {
            $startRow = 31;
            $currentRow = $startRow;
            $lines = $this->htmlToLines($htmlObservations);
            
            foreach ($lines as $line) {
                if (!empty(trim($line['text']))) {
                    if ($line['isBold']) {
                        $richText = new RichText();
                        $run = $richText->createTextRun($line['text']);
                        $run->getFont()->setBold(true);
                        if ($line['isHeader']) {
                            $run->getFont()->setSize(11);
                        }
                        $sheet->setCellValue("B{$currentRow}", $richText);
                    } else {
                        $sheet->setCellValue("B{$currentRow}", $line['text']);
                    }
                    $currentRow++;
                }
            }
        }

        // 6️⃣ Datos del empleado
        if ($employee) {
            $sheet->setCellValue('J57', $employee->first_name . ' ' . $employee->last_name);
            $sheet->setCellValue('I58', $employee->document_type ?? '');
            $sheet->setCellValueExplicit('J58', $employee->document_number ?? '', DataType::TYPE_STRING);
        }
        
        $sheet->setCellValue('E57', $compliance->fullname_cliente ?? '');
        $sheet->setCellValue('C58', $compliance->document_type ?? '');
        $sheet->setCellValueExplicit('E58', $compliance->document_number ?? '', DataType::TYPE_STRING);

        // 7️⃣ Firmas
        if (!empty($compliance->client_signature)) {
            $this->addBase64ImageToCell($sheet, $compliance->client_signature, 'E56', 150, 50);
        }
        if (!empty($compliance->employee_signature)) {
            $this->addBase64ImageToCell($sheet, $compliance->employee_signature, 'J56', 150, 50);
        }

        return $spreadsheet;
    }

    private function addBase64ImageToCell($sheet, string $base64Data, string $cell, int $width = 150, int $height = 50): void
    {
        // Remover el prefijo data:image/png;base64, si existe
        $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        
        // Decodificar base64
        $imageData = base64_decode($base64Data);
        
        if ($imageData === false) {
            return;
        }

        // Crear imagen desde string
        $originalImage = @imagecreatefromstring($imageData);
        
        if ($originalImage === false) {
            return;
        }

        // Obtener dimensiones originales
        $origWidth = imagesx($originalImage);
        $origHeight = imagesy($originalImage);

        // Crear nueva imagen con fondo blanco
        $imageWithBackground = imagecreatetruecolor($origWidth, $origHeight);
        
        // Definir color blanco y rellenar el fondo
        $white = imagecolorallocate($imageWithBackground, 255, 255, 255);
        imagefill($imageWithBackground, 0, 0, $white);
        
        // Copiar la imagen original sobre el fondo blanco (preservando transparencia)
        imagecopy($imageWithBackground, $originalImage, 0, 0, 0, 0, $origWidth, $origHeight);
        
        // Liberar memoria de la imagen original
        imagedestroy($originalImage);

        // Crear MemoryDrawing con la imagen que tiene fondo blanco
        $drawing = new MemoryDrawing();
        $drawing->setName('Firma');
        $drawing->setDescription('Firma');
        $drawing->setImageResource($imageWithBackground);
        $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_PNG);
        $drawing->setCoordinates($cell);
        $drawing->setWidth($width);
        $drawing->setHeight($height);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }
    private function htmlToLines(string $html): array
    {
        $lines = [];
        
        // Procesar encabezados (h2, h3) - cada uno en su propia línea
        $html = preg_replace_callback('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/si', function($matches) use (&$lines) {
            $text = strip_tags($matches[1]);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);
            if (!empty($text)) {
                $lines[] = [
                    'text' => $text,
                    'isBold' => true,
                    'isHeader' => true,
                ];
            }
            return ''; // Remover del HTML
        }, $html);

        // Procesar listas ordenadas (ol)
        $html = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/si', function($matches) use (&$lines) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $matches[1], $items);
            $counter = 1;
            foreach ($items[1] as $item) {
                $text = strip_tags($item);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $text = trim($text);
                if (!empty($text)) {
                    $lines[] = [
                        'text' => "{$counter}. {$text}",
                        'isBold' => false,
                        'isHeader' => false,
                    ];
                    $counter++;
                }
            }
            return '';
        }, $html);

        // Procesar listas no ordenadas (ul)
        $html = preg_replace_callback('/<ul[^>]*>(.*?)<\/ul>/si', function($matches) use (&$lines) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $matches[1], $items);
            foreach ($items[1] as $item) {
                $text = strip_tags($item);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $text = trim($text);
                if (!empty($text)) {
                    $lines[] = [
                        'text' => "• {$text}",
                        'isBold' => false,
                        'isHeader' => false,
                    ];
                }
            }
            return '';
        }, $html);

        // Procesar párrafos con strong/bold
        $html = preg_replace_callback('/<p[^>]*>(.*?)<\/p>/si', function($matches) use (&$lines) {
            $content = $matches[1];
            $isBold = preg_match('/<(strong|b)[^>]*>/i', $content);
            $text = strip_tags($content);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);
            if (!empty($text)) {
                $lines[] = [
                    'text' => $text,
                    'isBold' => $isBold,
                    'isHeader' => false,
                ];
            }
            return '';
        }, $html);

        // Procesar blockquotes
        $html = preg_replace_callback('/<blockquote[^>]*>(.*?)<\/blockquote>/si', function($matches) use (&$lines) {
            $text = strip_tags($matches[1]);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);
            if (!empty($text)) {
                $lines[] = [
                    'text' => "» {$text}",
                    'isBold' => false,
                    'isHeader' => false,
                ];
            }
            return '';
        }, $html);

        // Procesar texto restante (sin etiquetas específicas)
        $remaining = strip_tags($html);
        $remaining = html_entity_decode($remaining, ENT_QUOTES, 'UTF-8');
        $remaining = trim($remaining);
        if (!empty($remaining)) {
            $lines[] = [
                'text' => $remaining,
                'isBold' => false,
                'isHeader' => false,
            ];
        }

        return $lines;
    }
}