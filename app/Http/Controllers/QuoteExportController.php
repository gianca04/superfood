<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf; // Cambiar a DomPDF
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class QuoteExportController extends Controller
{
    public function exportPdf(Quote $quote)
    {
        // 1. Cargar relaciones
        $quote->load(['employee', 'subClient', 'quoteCategory', 'quoteDetails.pricelist.unit',  'project']);

        // 2. Preparar datos (misma lógica que el preview)
        $ceco = $quote->subClient->ceco ?? $quote->ceco ?? '----------';
        $groupedDetails = $quote->quoteDetails->groupBy('item_type');
        $formattedId = str_pad($quote->id, 5, '0', STR_PAD_LEFT);

        $sections = [
            'VIATICOS'   => 'VIATICOS',
            'SUMINISTRO' => 'SUMINISTRO',
            'MANO DE OBRA' => 'MANO DE OBRA',
            'SERVICIO'   => 'SERVICIO'
        ];

        $itemsData = collect();
        $sectionIndex = 1;

        foreach ($sections as $type => $label) {
            if ($groupedDetails->has($type)) {
                $itemsData->push(['tipo' => 'header', 'numero' => $sectionIndex++, 'nombre' => $label]);
                // Ordenar los detalles por 'line' antes de añadirlos
                $sortedDetails = $groupedDetails->get($type)->sortBy('line');
                foreach ($sortedDetails as $detail) {
                    $itemsData->push([
                        'tipo'        => 'item',
                        'line'        => $detail->line,  // Añadido para incluir el número de línea
                        'linea'       => $detail->pricelist->sat_line ?? '-',
                        'descripcion' => $detail->pricelist->sat_description ?? 'Sin descripción',
                        'comentario'  => $detail->comment ?? '-',
                        'unidad'      => $detail->pricelist->unit->name ?? 'UND',
                        'cantidad'    => $detail->quantity,
                        'pu'          => $detail->unit_price,
                        'subtotal'    => $detail->subtotal,
                    ]);
                }
            }
        }

        $data = [
            'original_id'       => $quote->id,
            'quote_id'          => $formattedId,
            'numero_cotizacion' => $quote->request_number,
            'servicio'          => $quote->project->name ?? $quote->quoteCategory->name ?? 'Sin servicio',
            'ruc_empresa'       => '20539249640',
            'empresa_nombre'    => 'SAT INDUSTRIALES',
            'cotizado_por'      => $quote->employee ? $quote->employee->short_name : 'No asignado',
            'n_solicitud'       => $quote->project && $quote->project->request_number ? $quote->project->request_number : '-',  // Ajustado para mostrar '-' si no hay request_number
            'cliente'           => $quote->subClient->name ?? 'Sin cliente',
            'jefe_energia'      => $quote->energy_sci_manager ?? '-',
            'fecha_cotizacion'  => $quote->quote_date ? $quote->quote_date->format('d/m/Y') : '-',
            'categoria'         => $quote->quoteCategory->name ?? '-',
            'ceco'              => $ceco,
            'fecha_ejecucion'   => $quote->execution_date ? $quote->execution_date->format('d/m/Y') : '-',
            'total_general'     => number_format($quote->total_amount, 2),
            'items'             => $itemsData,
            'isPdf'             => true, // Marcador para la vista
        ];
        $html = view('filament.resources.quote-resource.pages.preview', $data)->render();

        // Usar DomPDF en lugar de mPDF
        $pdf = Pdf::loadHtml($html)->setPaper('a4', 'landscape');
        return $pdf->download("Cotizacion_{$quote->request_number}.pdf");
    }
    public function exportExcel(Quote $quote)
    {
        // Cargar plantilla
        $templatePath = app_path('Documents/formatoCotizacion.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Cargar relaciones necesarias
        $quote->load(['employee', 'subClient', 'quoteCategory', 'quoteDetails.pricelist.unit', 'project']);

        // Formatear el ID
        $formattedId = str_pad($quote->id, 5, '0', STR_PAD_LEFT);

        // Asignar datos a celdas principales
        $sheet->setCellValue('H1', $formattedId);
        $sheet->setCellValue('C3', $quote->project->name ?? ($quote->quoteCategory->name ?? ''));
        $sheet->setCellValue('E3', $quote->request_number ?? '');
        $sheet->setCellValue('E4', $quote->subClient->name ?? '');
        $sheet->setCellValue('H3', $quote->quoteCategory->name ?? '');
        $sheet->setCellValue('H4', $quote->subClient->ceco ?? $quote->ceco ?? '');
        $sheet->setCellValue('C6', $quote->employee ? $quote->employee->short_name : '');
        $sheet->setCellValue('E5', $quote->energy_sci_manager ?? '');
        $sheet->setCellValue('E6', $quote->quote_date ? $quote->quote_date->format('d/m/Y') : '');
        $sheet->setCellValue('H5', $quote->execution_date ? $quote->execution_date->format('d/m/Y') : '');
        // Calcular el total de subtotales
        $total = $quote->quoteDetails->sum(function ($detail) {
            return $detail->subtotal ?? ($detail->quantity * $detail->unit_price);
        });
        $sheet->setCellValue('H6', 'S/ ' . number_format($total, 2));

        // --- ITEMS ---
        $groupedDetails = $quote->quoteDetails->groupBy('item_type');
        $sections = [
            'VIATICOS'   => 'VIATICOS',
            'SUMINISTRO' => 'SUMINISTRO',
            'MANO DE OBRA' => 'MANO DE OBRA',
            'SERVICIO'   => 'SERVICIO'
        ];

        $currentRow = 9; // Comenzamos en la fila 9
        $sectionIndex = 1;
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');

        foreach ($sections as $type => $label) {
            if ($groupedDetails->has($type)) {
                // Encabezado de sección con número y nombre
                $sheet->setCellValue('A' . $currentRow, $sectionIndex);
                $sheet->mergeCells("B{$currentRow}:H{$currentRow}");
                $sheet->setCellValue("B{$currentRow}", $label);
                // Color de fondo
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6E0B4');
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setBold(true)->setName('Calibri')->setSize(11);

                $currentRow++;
                // Ordenar los detalles por 'line' antes de añadirlos
                $sortedDetails = $groupedDetails->get($type)->sortBy('line');
                foreach ($sortedDetails as $detail) {
                    // A: line, B: línea, C: descripción, D: comentario, E: unidad, F: cantidad, G: P.U., H: subtotal
                    $sheet->setCellValue("A{$currentRow}", $detail->line);
                    $sheet->setCellValue("B{$currentRow}", $detail->pricelist->sat_line ?? '');
                    $sheet->setCellValue("C{$currentRow}", $detail->pricelist->sat_description ?? '');
                    $sheet->setCellValue("D{$currentRow}", $detail->comment ?? '');
                    $sheet->setCellValue("E{$currentRow}", $detail->pricelist->unit->name ?? 'UND');
                    $sheet->setCellValue("F{$currentRow}", $detail->quantity);
                    $sheet->setCellValue("G{$currentRow}", 'S/ ' . number_format($detail->unit_price, 2));
                    $sheet->setCellValue("H{$currentRow}", 'S/ ' . number_format($detail->subtotal, 2));
                    // Estilo Calibri 11 y ajuste de texto
                    $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setName('Calibri')->setSize(11);
                    $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getAlignment()->setWrapText(true);
                    // Ajustar altura de fila para que se estire según el contenido
                    $sheet->getRowDimension($currentRow)->setRowHeight(-1);
                    $currentRow++;
                }
                $sectionIndex++;
            }
        }

        // Pintar bordes de la tabla hasta la última fila de datos llenados
        $lastRow = $currentRow - 1;
        $tableRange = "A9:H{$lastRow}";
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle($tableRange)->applyFromArray($styleArray);

        // Ajustar ancho de columnas para mejor visualización general (excepto descripción y comentario)
        foreach (['A', 'B', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Fijar ancho de columna para descripción y comentario para que la fila se estire
        $sheet->getColumnDimension('C')->setWidth(35); // Descripción
        $sheet->getColumnDimension('D')->setWidth(25); // Comentario

        // Descargar archivo
        $filename = 'Cotizacion_' . ($quote->request_number ?? $quote->id) . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save('php://output');
        exit;
    }
}
