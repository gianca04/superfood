<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ExcelExportController extends Controller
{
    public function downloadActaExcel($id)
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

        // 2️⃣ Cargar plantilla
        $templatePath = app_path('Documents/formatoActaConformidad.xlsx');
        
        if (!file_exists($templatePath)) {
            abort(404, 'Plantilla no encontrada');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('L2', 'N° ' . str_pad($compliance->id, 6, '0', STR_PAD_LEFT));

        // 3️⃣ Llenar datos en las celdas (ajusta según tu plantilla)
        $sheet->setCellValue('E7', $client?->business_name ?? '');
        $sheet->setCellValueExplicit('J7', $client?->document_number ?? '', DataType::TYPE_STRING);
        $sheet->setCellValue('E9', $subClient?->name ?? '');
        $sheet->setCellValue('J9', $subClient?->address ?? '');
        $sheet->setCellValue('B12', $quote?->correlative ?? 'OT-' . ($project?->id ?? ''));
        $sheet->setCellValue('E12', $quote?->project_description ?? $project?->name ?? '');
        $sheet->setCellValue('E15', $project?->start_date?->format('d/m/Y') ?? '');
        $sheet->setCellValue('K15', $project?->end_date?->format('d/m/Y') ?? '');

        // 4️⃣ Llenar datos de activos
        $assets = $compliance->assets ?? [];

        // Mapeo de activos: clave => [fila, nombre con checkbox]
        $assetsMap = [
            'tablero_autosoportado' => [
                'row' => 24,
                'name' => 'Tablero Autosoportado',
            ],
            'tablero_adosados' => [
                'row' => 25,
                'name' => 'Tablero Adosados',
            ],
            'banco_condensadores' => [
                'row' => 26,
                'name' => 'Banco de Condensadores',
            ],
            'pozos_baja_tension' => [
                'row' => 27,
                'name' => 'Pozos a Tierra Baja Tensión',
            ],
            'pozos_media_tension' => [
                'row' => 28,
                'name' => 'Pozos a Tierra Media Tensión',
            ],
        ];

        foreach ($assetsMap as $key => $config) {
            $row = $config['row'];
            $name = $config['name'];
            $assetData = $assets[$key] ?? [];
            
            $isSelected = $assetData['selected'] ?? false;
            
            // Columna C: Nombre con checkbox
            if ($isSelected) {
                $sheet->setCellValue("C{$row}", "{$name}           ( X )");
                
                // Columna I: Cantidad
                $sheet->setCellValue("I{$row}", $assetData['quantity'] ?? '');
                
                // Columna K: Comentarios
                $sheet->setCellValue("K{$row}", $assetData['comments'] ?? '');
            } else {
                $sheet->setCellValue("C{$row}", "{$name}           (    )");
            }
        }

        $htmlObservations = $compliance->maintenance_observations ?? '';
        if (!empty($htmlObservations)) {
            $richText = $this->htmlToRichText($htmlObservations);
            $sheet->setCellValue('B31', $richText);
            
            // Ajustar altura de fila y wrap text
            $sheet->getRowDimension(31)->setRowHeight(-1); // Auto height
            $sheet->getStyle('B31')->getAlignment()->setWrapText(true);
        }
        // 5️⃣ Descargar
        $filename = 'Acta_Conformidad_' . ($quote?->correlative ?? $compliance->id) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    private function htmlToRichText(string $html): RichText
    {
        $richText = new RichText();
        
        // Limpiar HTML y convertir a texto con formato
        $html = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);
        $html = str_replace(['</p>', '</div>', '</h1>', '</h2>', '</h3>', '</li>'], "\n", $html);
        $html = str_replace('<li>', '• ', $html);
        
        // Procesar listas ordenadas (ol)
        $html = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/s', function($matches) {
            $items = preg_split('/<li[^>]*>/', $matches[1]);
            $result = '';
            $counter = 1;
            foreach ($items as $item) {
                $item = trim(strip_tags($item));
                if (!empty($item)) {
                    $result .= "{$counter}. {$item}\n";
                    $counter++;
                }
            }
            return $result;
        }, $html);
        
        // Procesar listas no ordenadas (ul) - ya manejado arriba con <li>
        
        // Procesar negritas y otros formatos
        // Separar por etiquetas de formato
        $pattern = '/(<strong>|<\/strong>|<b>|<\/b>|<em>|<\/em>|<i>|<\/i>|<u>|<\/u>|<h[1-6][^>]*>|<\/h[1-6]>)/i';
        $parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $isBold = false;
        $isItalic = false;
        $isUnderline = false;
        $isHeader = false;
        
        foreach ($parts as $part) {
            $lowerPart = strtolower($part);
            
            // Detectar apertura/cierre de etiquetas
            if ($lowerPart === '<strong>' || $lowerPart === '<b>') {
                $isBold = true;
                continue;
            }
            if ($lowerPart === '</strong>' || $lowerPart === '</b>') {
                $isBold = false;
                continue;
            }
            if ($lowerPart === '<em>' || $lowerPart === '<i>') {
                $isItalic = true;
                continue;
            }
            if ($lowerPart === '</em>' || $lowerPart === '</i>') {
                $isItalic = false;
                continue;
            }
            if ($lowerPart === '<u>') {
                $isUnderline = true;
                continue;
            }
            if ($lowerPart === '</u>') {
                $isUnderline = false;
                continue;
            }
            if (preg_match('/<h[1-6]/i', $lowerPart)) {
                $isHeader = true;
                $isBold = true;
                continue;
            }
            if (preg_match('/<\/h[1-6]>/i', $lowerPart)) {
                $isHeader = false;
                $isBold = false;
                continue;
            }
            
            // Limpiar texto restante
            $text = strip_tags($part);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);
            
            if (empty($text)) {
                continue;
            }
            
            // Crear run con formato
            $run = $richText->createTextRun($text);
            
            if ($isBold || $isHeader) {
                $run->getFont()->setBold(true);
            }
            if ($isItalic) {
                $run->getFont()->setItalic(true);
            }
            if ($isUnderline) {
                $run->getFont()->setUnderline(true);
            }
            if ($isHeader) {
                $run->getFont()->setSize(12); // Tamaño más grande para headers
            }
        }
        
        return $richText;
    }
}