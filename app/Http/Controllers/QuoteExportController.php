<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Mpdf\Mpdf;
use Illuminate\Http\Request;

class QuoteExportController extends Controller
{
    public function exportPdf(Quote $quote)
    {
        // 1. Cargar relaciones
        $quote->load(['employee', 'subClient', 'quoteCategory', 'quoteDetails.pricelist.unit']);

        // 2. Preparar datos (misma lógica que el preview)
        $ceco = $quote->subClient->ceco ?? $quote->ceco ?? '----------';
        $groupedDetails = $quote->quoteDetails->groupBy('item_type');
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
                foreach ($groupedDetails->get($type) as $detail) {
                    $itemsData->push([
                        'tipo'        => 'item',
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
            'numero_cotizacion' => $quote->request_number,
            'servicio'          => $quote->service_name ?? $quote->quoteCategory->name ?? 'Sin servicio',
            'ruc_empresa'       => '20539249640',
            'empresa_nombre'    => 'SAT INDUSTRIALES',
            'cotizado_por'      => $quote->employee ? ($quote->employee->first_name . ' ' . $quote->employee->last_name) : 'No asignado',
            'n_solicitud'       => $quote->request_number,
            'cliente'           => $quote->subClient->name ?? 'Sin cliente',
            'jefe_energia'      => $quote->energy_sci_manager ?? '-',
            'fecha_cotizacion'  => $quote->quote_date ? $quote->quote_date->format('d/m/Y') : '-',
            'categoria'         => $quote->quoteCategory->name ?? '-',
            'ceco'              => $ceco,
            'fecha_ejecucion'   => $quote->execution_date ? $quote->execution_date->format('d/m/Y') : '-',
            'total_general'     => number_format($quote->total_amount, 2),
            'items'             => $itemsData,
        ];

        // 3. Generar HTML desde la vista
        $html = view('filament.resources.quote-resource.pages.preview', $data)->render();

        // 4. Configurar mPDF
        $mpdf = new Mpdf([
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'format' => 'A4',
            'tempDir' => storage_path('app/public') // Directorio temporal para evitar errores de permisos
        ]);

        $mpdf->SetTitle("Cotización " . $quote->request_number);
        $mpdf->WriteHTML($html);

        // 5. Descargar archivo
        return $mpdf->Output("Cotizacion_{$quote->request_number}.pdf", 'D');
    }
}
