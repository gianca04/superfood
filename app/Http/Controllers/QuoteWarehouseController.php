<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteWarehouseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreQuoteWarehouseDetailRequest;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // Asegúrate de tener instalado barryvdh/laravel-dompdf

/**
 * Controlador para manejar las operaciones CRUD de cotizaciones (Quotes).
 */
class QuoteWarehouseController extends Controller
{

    /**
     * Muestra la vista previa de una cotización/ITEMS para almacén.
     *
     * @param \App\Models\QuoteWarehouse $quoteWarehouse
     * @return \Illuminate\View\View
     */
    public function preview(\App\Models\QuoteWarehouse $quoteWarehouse, Request $request)
    {
        $quote = $quoteWarehouse->quote;
        $quote->load([
            'subClient',
            'quoteDetails.pricelist.unit'
        ]);

        // Obtener los detalles atendidos por almacén (quote_warehouse_details)
        $warehouseDetails = \App\Models\QuoteWarehouseDetail::where('quote_warehouse_id', $quoteWarehouse->id)
            ->get()
            ->keyBy('quote_detail_id');

        $groupedDetails = $quote->quoteDetails->groupBy('item_type');

        $details = [];
        foreach (['VIATICOS', 'SUMINISTRO', 'MANO DE OBRA'] as $type) {
            if ($groupedDetails->has($type)) {
                foreach ($groupedDetails[$type] as $detail) {
                    $attended = $warehouseDetails[$detail->id]->attended_quantity ?? 0;
                    $details[] = [
                        'item_type'        => $type,
                        'quote_detail_id'  => $detail->id,
                        'sat_line'         => $detail->pricelist->sat_line ?? '',
                        'sat_description'  => $detail->pricelist->sat_description ?? '',
                        'quantity'         => $detail->quantity,
                        'unit_price'       => $detail->unit_price,
                        'subtotal'         => $detail->subtotal,
                        'unit_name'        => $detail->pricelist->unit->name ?? '',
                        'entregado'        => $attended, // <-- Aquí se pasa el dato entregado
                    ];
                }
            }
        }

        return view('filament.resources.quote-warehouse-resource.pages.list', [
            'quote'        => $quote,
            'client'       => $quote->subClient->name ?? '',
            'details'      => $details,
            'quoteWarehouse' => $quoteWarehouse,
            'satLine'      =>  $detail->pricelist->sat_line ?? '',
            'description'  => $detail->pricelist->sat_description ?? '',
        ]);
    }

    /**
     * Guarda el detalle atendido de almacén.
     */
    public function store(\App\Http\Requests\StoreQuoteWarehouseDetailRequest $request)
    {
        try {
            $quoteWarehouse = \App\Models\QuoteWarehouse::findOrFail($request->input('quote_warehouse_id'));
            $quoteWarehouse->observations = $request->input('observations');

            // Obtener el progreso total enviado desde la vista
            $progresoTotal = $request->input('progreso_total', 0);
            Log::info('Progreso total recibido:', ['progreso_total' => $progresoTotal]);

            // Variable para almacenar el mensaje de cambio de estado
            $estadoMensaje = null;

            // Validar que el progreso total se reciba correctamente
            if ($progresoTotal > 0 && $progresoTotal < 100) {
                if ($quoteWarehouse->status !== 'Parcial') {
                    $estadoMensaje = 'El estado ha cambiado a Parcial.';
                }
                $quoteWarehouse->status = 'Parcial';
            } elseif ($progresoTotal === 100) {
                if ($quoteWarehouse->status !== 'Atendido') {
                    $estadoMensaje = 'El estado ha cambiado a Atendido.';
                    $quoteWarehouse->attended_at = now(); // Guardar la fecha y hora actual solo si es la primera vez
                }
                $quoteWarehouse->status = 'Atendido';
            }

            $quoteWarehouse->save();

            $details = $request->input('details', []);
            $errores = [];
            $guardados = 0;

            // Si no hay detalles, solo guardar las observaciones y salir
            if (empty($details)) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Observaciones guardadas correctamente!',
                    'estadoMensaje' => $estadoMensaje,
                ]);
            }

            foreach ($details as $i => $detail) {
                // Solo guardar si a_despachar > 0 y quote_detail_id existe
                if (
                    !isset($detail['quote_detail_id']) ||
                    !isset($detail['a_despachar']) ||
                    $detail['a_despachar'] <= 0
                ) {
                    continue;
                }

                Log::info('Detalle recibido', [
                    'quote_warehouse_id' => $quoteWarehouse->id,
                    'quote_detail_id'    => $detail['quote_detail_id'],
                    'attended_quantity'  => $detail['a_despachar'],
                ]);

                try {
                    // Verificar si ya existe un registro para este quote_detail_id
                    $detalleExistente = \App\Models\QuoteWarehouseDetail::where('quote_warehouse_id', $quoteWarehouse->id)
                        ->where('quote_detail_id', $detail['quote_detail_id'])
                        ->first();

                    if ($detalleExistente) {
                        // Si ya existe, sumamos el nuevo valor al attended_quantity existente
                        $detalleExistente->update([
                            'attended_quantity' => $detalleExistente->attended_quantity + $detail['a_despachar'],
                        ]);
                    } else {
                        // Si no existe, creamos un nuevo registro
                        \App\Models\QuoteWarehouseDetail::create([
                            'quote_warehouse_id' => $quoteWarehouse->id,
                            'quote_detail_id'    => $detail['quote_detail_id'],
                            'attended_quantity'  => $detail['a_despachar'],
                        ]);
                    }

                    $guardados++;
                } catch (\Exception $e) {
                    $errores[] = "Error en el detalle #" . ($i + 1) . ": " . $e->getMessage();
                }
            }

            // Mensaje si no se guardó ningún detalle
            if ($guardados === 0 && count($errores) === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Observaciones guardadas correctamente! No se guardó ningún detalle.',
                    'estadoMensaje' => $estadoMensaje,
                ]);
            }

            if (count($errores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos detalles no se guardaron correctamente.',
                    'errors'  => $errores,
                    'estadoMensaje' => $estadoMensaje,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "¡Despacho guardado correctamente! Se actualizaron $guardados detalles.",
                'estadoMensaje' => $estadoMensaje,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera un PDF de la vista de atención de suministros.
     *
     * @param \App\Models\QuoteWarehouse $quoteWarehouse
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(\App\Models\QuoteWarehouse $quoteWarehouse)
    {
        $quote = $quoteWarehouse->quote;
        $quote->load(['subClient', 'quoteDetails.pricelist.unit']);

        // Obtener los detalles atendidos por almacén (quote_warehouse_details)
        $warehouseDetails = \App\Models\QuoteWarehouseDetail::where('quote_warehouse_id', $quoteWarehouse->id)
            ->get()
            ->keyBy('quote_detail_id');

        $groupedDetails = $quote->quoteDetails->groupBy('item_type');

        $details = [];
        foreach (['VIATICOS', 'SUMINISTRO', 'MANO DE OBRA'] as $type) {
            if ($groupedDetails->has($type)) {
                foreach ($groupedDetails[$type] as $detail) {
                    $attended = $warehouseDetails[$detail->id]->attended_quantity ?? 0;
                    $details[] = [
                        'item_type'        => $type,
                        'quote_detail_id'  => $detail->id,
                        'sat_line'         => $detail->pricelist->sat_line ?? '',
                        'sat_description'  => $detail->pricelist->sat_description ?? '',
                        'quantity'         => $detail->quantity,
                        'unit_price'       => $detail->unit_price,
                        'subtotal'         => $detail->subtotal,
                        'unit_name'        => $detail->pricelist->unit->name ?? '',
                        'entregado'        => $attended,
                    ];
                }
            }
        }

        // Obtener el logo del cliente si existe
        $clientLogo = $quote->subClient->logo ?? null;

        // Generar el PDF
        $pdf = Pdf::loadView('filament.resources.quote-warehouse-resource.pages.pdf', [
            'quoteWarehouse' => $quoteWarehouse,
            'quote'          => $quote,
            'clientName'     => $quote->subClient->name ?? 'Sin Cliente',
            'quoteDate'      => $quote->quote_date ? $quote->quote_date->format('d/m/Y') : 'N/A',
            'executionDate'  => $quote->execution_date ? $quote->execution_date->format('d/m/Y') : 'N/A',
            'status'         => $quoteWarehouse->status,
            'details'        => $details,
            'clientLogo'     => $clientLogo ? public_path('storage/' . $clientLogo) : null,
            'observations'   => $quoteWarehouse->observations, // Agregar observaciones
        ]);

        return $pdf->stream('Atencion_Suministros.pdf');
    }
}
