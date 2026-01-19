<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\Project;
use App\Services\CloudConvertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ExcelExportController extends Controller
{
    public function downloadAsExcel(Spreadsheet $spreadsheet, string $filename)
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadAutoActaOrReports($id)
    {
        // 1. Buscar si el ID es de un acta o de un proyecto
        $compliance = Compliance::find($id);
        $project = Project::find($id);

        if ($compliance) {
            // Si es un acta, obtener el proyecto relacionado
            $project = $compliance->project;
        } elseif ($project) {
            // Si es un proyecto, buscar el acta relacionada
            $compliance = $project->compliance;
        }

        if (!$project) {
            abort(404, 'Proyecto no encontrado');
        }

        $workReportsCount = $project->workReports()->count();

        if ($compliance && $workReportsCount > 0) {
            // Descargar acta + reportes
            return redirect()->route('actas.pdf-with-reports', $compliance->id);
        } elseif ($compliance) {
            // Solo acta
            return redirect()->route('actas.pdf', $compliance->id);
        } elseif ($workReportsCount > 0) {
            // Solo reportes
            return redirect()->route('work-reports.download-multiple-pdf', $project->id);
        } else {
            abort(404, 'No hay acta ni reportes de trabajo para este proyecto.');
        }
    }

    /**
     * Descarga el Acta de Conformidad como PDF usando CloudConvert
     */
    // ==================== ACTA DE CONFORMIDAD ====================

    public function downloadActaWithReports($id)
    {
        $tempBasePath = storage_path('app');
        $currentUser = exec('whoami'); // Obtiene el usuario del sistema (ej: www-data)

        Log::info('🛠️ INICIO DEBUG PDF MERGE', [
            'id' => $id,
            'usuario_sistema' => $currentUser,
            'ruta_base_intentada' => $tempBasePath,
            'existe_carpeta' => file_exists($tempBasePath) ? 'SI' : 'NO',
            'es_escribible' => is_writable($tempBasePath) ? 'SI' : 'NO',
            'permisos_linux' => substr(sprintf('%o', fileperms($tempBasePath)), -4),
        ]);
        try {
            // --- PASO 1: GENERAR EL ACTA (mPDF) ---
            $actaData = $this->getActaData($id);
            $logoPath = public_path('images/Logo2.png');
            if (file_exists($logoPath)) {
                $actaData['logo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
            $htmlActa = view('filament.pdf.acta_conformidad_pdf', $actaData)->render();

            $mpdfConfig = [
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'tempDir' => $tempBasePath // <--- Forzamos la carpeta temporal aquí
            ];

            Log::info('⚙️ Configurando mPDF con tempDir:', ['dir' => $mpdfConfig['tempDir']]);

            $mpdfActa = new \Mpdf\Mpdf($mpdfConfig);
            $mpdfActa->WriteHTML($htmlActa);

            $actaPath = storage_path('app/temp_acta_' . $id . '_' . time() . '.pdf');
            $mpdfActa->Output($actaPath, 'F');

            // --- PASO 2: GENERAR LOS REPORTES (DomPDF) ---
            $compliance = \App\Models\Compliance::findOrFail($id);
            $workReports = \App\Models\WorkReport::where('project_id', $compliance->project_id)
                ->with(['employee', 'project.subClient.client', 'photos'])
                ->get();

            if ($workReports->isEmpty()) {
                // Si no hay reportes, solo descargamos el Acta para evitar errores de archivo vacío
                return $this->downloadActaPdf($id);
            }

            $htmlReports = '';
            foreach ($workReports as $report) {
                $dataReport = app(\App\Http\Controllers\WorkReportExcelController::class)->prepareDataForBladePdf($report);
                $htmlReports .= view('reports.report-work', $dataReport)->render();
            }

            $dompdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlReports)
                ->setPaper('a4', 'portrait')
                ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

            $reportsPath = $tempBasePath . '/temp_reports_' . $id . '_' . time() . '.pdf';
            Log::info('📄 Intentando crear archivo Reportes:', ['path' => $reportsPath]);

            $reportsPath = storage_path('app/temp_reports_' . $id . '_' . time() . '.pdf');
            file_put_contents($reportsPath, $dompdf->output());
            Log::info('🔗 Iniciando Merge de PDFs...');
            // --- PASO 3: UNIR AMBOS (MERGE usando el mPDF principal) ---
            // Creamos la instancia que servirá de contenedor final
            $finalMpdf = new \Mpdf\Mpdf(['format' => 'A4']);

            // Importar Acta
            $pageCount = $finalMpdf->setSourceFile($actaPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $finalMpdf->importPage($i);
                $finalMpdf->AddPage();
                $finalMpdf->useTemplate($tplId);
            }

            // Importar Reportes
            $pageCount = $finalMpdf->setSourceFile($reportsPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $finalMpdf->importPage($i);
                $finalMpdf->AddPage();
                $finalMpdf->useTemplate($tplId);
            }

            // Generar el PDF final en memoria
            $pdfOutput = $finalMpdf->Output('', 'S');

            // --- PASO 4: LIMPIEZA ---
            if (file_exists($actaPath)) unlink($actaPath);
            if (file_exists($reportsPath)) unlink($reportsPath);

            $filename = 'Acta_y_Reportes_' . $id . '_' . now()->format('YmdHis') . '.pdf';

            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            // === 🔴 LOG DETALLADO DEL ERROR ===
            Log::error("❌ CRASH en downloadActaWithReports", [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace_resumido' => substr($e->getTraceAsString(), 0, 500)
            ]);
            return back()->with('error', 'Ocurrió un error al generar el documento combinado.');
        }
    }

    public function downloadActaPdf($id)
    {
        try {
            Log::info('🔍 Iniciando descarga PDF - ID Compliance: ' . $id);

            // Paso 1: Obtener datos
            Log::info('📊 Obteniendo datos de Compliance...');
            $data = $this->getActaData($id);

            // Añadir logo como base64 para mPDF
            $logoPath = public_path('images/Logo2.png');
            if (file_exists($logoPath)) {
                $data['logo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            } else {
                $data['logo_base64'] = null;
            }

            Log::info('✅ Datos obtenidos correctamente', ['keys' => array_keys($data)]);

            // Paso 2: Generar HTML desde la vista Blade para PDF
            Log::info('🎨 Renderizando vista Blade: filament.pdf.acta_conformidad_pdf');
            $html = view('filament.pdf.acta_conformidad_pdf', $data)->render();
            Log::info('✅ HTML generado correctamente', ['html_length' => strlen($html)]);

            // Paso 3: Crear directorio temporal si no existe
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                Log::info('📁 Creando directorio temporal: ' . $tempDir);
                mkdir($tempDir, 0755, true);
            }

            // Paso 4: Configurar mPDF
            Log::info('⚙️ Configurando mPDF...');
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'default_font' => 'Arial',
                'tempDir' => $tempDir,
            ]);
            Log::info('✅ mPDF configurado correctamente');

            // Paso 5: Escribir HTML en el PDF
            Log::info('📝 Escribiendo HTML en PDF...');
            $mpdf->WriteHTML($html);
            Log::info('✅ HTML escrito en PDF correctamente');

            // Paso 6: Obtener nombre del archivo
            $filename = $this->getActaFilename($id);
            Log::info('📄 Nombre de archivo generado: ' . $filename);

            // Paso 7: Generar salida PDF
            Log::info('🖨️ Generando salida PDF...');
            $pdfOutput = $mpdf->Output('', 'S');
            Log::info('✅ PDF generado correctamente', ['size' => strlen($pdfOutput) . ' bytes']);

            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al generar PDF: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Previsualiza el Acta de Conformidad en formato HTML
     */
    public function previewActaPdf($id)
    {
        Log::info('👁️ Iniciando previsualización PDF - ID: ' . $id);
        $data = $this->getActaData($id);
        $data['id'] = $id;
        $data['isPreview'] = true; // Marcar como previsualización para evitar bucles
        Log::info('✅ Previsualización preparada');
        return view('filament.pdf.acta_conformidad', $data);
    }

    public function downloadActaExcel($id)
    {
        try {
            Log::info('📊 Iniciando descarga Excel - ID Compliance: ' . $id);

            Log::info('🔨 Generando Spreadsheet...');
            $spreadsheet = $this->generateActaSpreadsheet($id);
            Log::info('✅ Spreadsheet generado correctamente');

            Log::info('📄 Obteniendo nombre de archivo...');
            $filename = $this->getActaFilename($id);
            Log::info('✅ Nombre de archivo: ' . $filename);

            Log::info('💾 Descargando Excel...');
            return $this->downloadAsExcel($spreadsheet, $filename);
        } catch (\Exception $e) {
            Log::error('❌ Error al descargar Excel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al generar Excel',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function getActaFilename($id): string
    {
        $compliance = Compliance::with('project.quote')->find($id);
        $quote = $compliance?->project?->quote;
        return 'Acta_Conformidad_' . ($quote?->correlative ?? $compliance->id);
    }

    /**
     * Obtiene los datos del Acta de Conformidad para la vista PDF
     */
    private function getActaData($id): array
    {
        try {
            Log::info('📋 Iniciando getActaData para ID: ' . $id);

            // Paso 1: Obtener Compliance
            Log::info('🔍 Buscando Compliance con relaciones...');
            $compliance = Compliance::with([
                'project.subClient.client',
                'project.quote'
            ])->findOrFail($id);
            Log::info('✅ Compliance encontrado', ['id' => $compliance->id]);

            // Paso 2: Extraer datos relacionados
            Log::info('🔗 Extrayendo datos relacionados...');
            $project = $compliance->project;
            $subClient = $project?->subClient;
            $client = $subClient?->client;
            $quote = $project?->quote;

            Log::info('✅ Datos relacionados extraídos', [
                'has_project' => !is_null($project),
                'has_subClient' => !is_null($subClient),
                'has_client' => !is_null($client),
                'has_quote' => !is_null($quote),
            ]);

            // Paso 3: Obtener usuario autenticado
            Log::info('👤 Obteniendo usuario autenticado...');
            $user = Auth::user();
            $employee = $user?->employee;
            Log::info('✅ Usuario obtenido', [
                'has_user' => !is_null($user),
                'has_employee' => !is_null($employee),
                'user_id' => $user?->id
            ]);

            // Paso 4: Preparar activos
            Log::info('🏗️ Preparando datos de activos...');
            $rawAssets = $compliance->assets ?? [];
            Log::info('📦 Assets brutos obtenidos', ['count' => count($rawAssets)]);

            $assetsConfig = [
                'tablero_autosoportado' => 'Tablero Autosoportado',
                'tablero_adosados' => 'Tablero Adosados',
                'banco_condensadores' => 'Banco de Condensadores',
                'pozos_baja_tension' => 'Pozos a Tierra Baja Tensión',
                'pozos_media_tension' => 'Pozos a Tierra Media Tensión',
            ];

            $assets = [];
            foreach ($assetsConfig as $key => $name) {
                $assetData = $rawAssets[$key] ?? [];
                $assets[] = [
                    'name' => $name,
                    'selected' => $assetData['selected'] ?? false,
                    'quantity' => $assetData['quantity'] ?? '',
                    'comments' => $assetData['comments'] ?? '',
                ];
            }
            Log::info('✅ Activos preparados', ['total_assets' => count($assets)]);

            // Paso 5: Construir array de datos
            Log::info('🔨 Construyendo array de datos finales...');
            $finalData = [
                'numero' => str_pad($compliance->id, 6, '0', STR_PAD_LEFT),
                'razon_social' => $client?->business_name ?? '',
                'ruc' => $client?->document_number ?? '',
                'tienda' => $subClient?->name ?? '',
                'direccion' => $subClient?->address ?? '',
                'numero_ot' => $project && $project->work_order_number
                    ? (str_starts_with($project->work_order_number, 'OT-')
                        ? $project->work_order_number
                        : 'OT-' . $project->work_order_number)
                    : '',
                // 'numero_ot' => $quote?->correlative ?? 'OT-' . ($project?->id ?? ''),
                'request_number' => $project?->request_number ?? '',
                'service_code' => $project?->service_code ?? '',
                'descripcion_servicio' => $quote?->project_description ?? $project?->name ?? '',
                'fecha_inicio' => $project?->start_date?->format('d/m/Y') ?? '',
                'fecha_fin' => $project?->end_date?->format('d/m/Y') ?? '',
                'assets' => $assets,
                'observaciones' => $compliance->maintenance_observations ?? '',
                'firma_cliente' => $compliance->client_signature ?? null,
                'cliente_nombre' => $compliance->fullname_cliente ?? '',
                'cliente_tipo_doc' => $compliance->document_type ?? 'DNI',
                'cliente_documento' => $compliance->document_number ?? '',
                'firma_empleado' => $compliance->employee_signature ?? null,
                'empleado_nombre' => $employee ? $employee->first_name . ' ' . $employee->last_name : '',
                'empleado_tipo_doc' => $employee?->document_type ?? 'DNI',
                'empleado_documento' => $employee?->document_number ?? '',
            ];
            Log::info('✅ Array de datos construido correctamente', ['keys' => array_keys($finalData)]);

            return $finalData;
        } catch (\Throwable $e) {
            Log::error('❌ Error en getActaData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function generateActaSpreadsheet($id)
    {
        try {
            Log::info('🔍 Iniciando generación de Spreadsheet para ID: ' . $id);
            Log::info('📋 Obteniendo datos de Compliance...');
            $compliance = Compliance::with([
                'project.subClient.client',
                'project.quote'
            ])->findOrFail($id);
            Log::info('✅ Compliance encontrado', ['id' => $compliance->id]);

            $project = $compliance->project;
            $subClient = $project?->subClient;
            $client = $subClient?->client;
            $quote = $project?->quote;
            Log::info('✅ Datos relacionados extraídos');

            $user = Auth::user();
            $employee = $user?->employee;
            Log::info('✅ Usuario autenticado', ['user_id' => $user?->id]);

            // 2️⃣ Cargar plantilla
            Log::info('📁 Buscando plantilla de Excel...');
            $templatePath = app_path('Documents/formatoActaConformidad.xlsx');
            Log::info('📍 Ruta de plantilla: ' . $templatePath);

            if (!file_exists($templatePath)) {
                Log::error('❌ Plantilla no encontrada en: ' . $templatePath);
                abort(404, 'Plantilla no encontrada');
            }
            Log::info('✅ Plantilla encontrada');

            Log::info('📖 Cargando archivo Excel...');
            $spreadsheet = IOFactory::load($templatePath);
            Log::info('✅ Archivo Excel cargado correctamente');

            $sheet = $spreadsheet->getActiveSheet();
            Log::info('✅ Hoja activa obtenida');

            // 3️⃣ Llenar datos
            Log::info('✏️ Llenando datos en el Spreadsheet...');
            $sheet->setCellValue('L2', 'N° ' . str_pad($compliance->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('E7', $client?->business_name ?? '');
            $sheet->setCellValueExplicit('J7', $client?->document_number ?? '', DataType::TYPE_STRING);
            $sheet->setCellValue('E9', $subClient?->name ?? '');
            $sheet->setCellValue('J9', $subClient?->address ?? '');
            $sheet->setCellValue('B12', $quote?->correlative ?? 'OT-' . ($project?->id ?? ''));
            $sheet->setCellValue('E12', $quote?->project_description ?? $project?->name ?? '');
            $sheet->setCellValue('E15', $project?->start_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue('K15', $project?->end_date?->format('d/m/Y') ?? '');
            Log::info('✅ Datos básicos completados');

            // 4️⃣ Activos
            Log::info('🏗️ Procesando activos...');
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
            Log::info('✅ Activos procesados');

            // 5️⃣ Observaciones
            Log::info('📝 Procesando observaciones...');
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
            Log::info('✅ Observaciones completadas');

            // 6️⃣ Datos del empleado
            Log::info('👤 Completando datos de empleado...');
            if ($employee) {
                $sheet->setCellValue('J57', $employee->first_name . ' ' . $employee->last_name);
                $sheet->setCellValue('I58', $employee->document_type ?? '');
                $sheet->setCellValueExplicit('J58', $employee->document_number ?? '', DataType::TYPE_STRING);
            }

            $sheet->setCellValue('E57', $compliance->fullname_cliente ?? '');
            $sheet->setCellValue('C58', $compliance->document_type ?? '');
            $sheet->setCellValueExplicit('E58', $compliance->document_number ?? '', DataType::TYPE_STRING);
            Log::info('✅ Datos de empleado completados');

            // 7️⃣ Firmas
            Log::info('🖋️ Procesando firmas...');
            if (!empty($compliance->client_signature)) {
                $this->addBase64ImageToCell($sheet, $compliance->client_signature, 'E56', 150, 50);
                Log::info('✅ Firma del cliente añadida');
            }
            if (!empty($compliance->employee_signature)) {
                $this->addBase64ImageToCell($sheet, $compliance->employee_signature, 'J56', 150, 50);
                Log::info('✅ Firma del empleado añadida');
            }

            Log::info('✅ Spreadsheet completado correctamente');
            return $spreadsheet;
        } catch (\Exception $e) {
            Log::error('❌ Error en generateActaSpreadsheet: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
        $html = preg_replace_callback('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/si', function ($matches) use (&$lines) {
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
        $html = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/si', function ($matches) use (&$lines) {
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
        $html = preg_replace_callback('/<ul[^>]*>(.*?)<\/ul>/si', function ($matches) use (&$lines) {
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
        $html = preg_replace_callback('/<p[^>]*>(.*?)<\/p>/si', function ($matches) use (&$lines) {
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
        $html = preg_replace_callback('/<blockquote[^>]*>(.*?)<\/blockquote>/si', function ($matches) use (&$lines) {
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
