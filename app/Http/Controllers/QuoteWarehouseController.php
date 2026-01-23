<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteWarehouseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreQuoteWarehouseDetailRequest;

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

        $groupedDetails = $quote->quoteDetails->groupBy('item_type');

        $details = [];
        foreach (['VIATICOS', 'SUMINISTRO', 'MANO DE OBRA'] as $type) {
            if ($groupedDetails->has($type)) {
                foreach ($groupedDetails[$type] as $detail) {
                    $details[] = [
                        'item_type'        => $type,
                        'sat_line'         => $detail->pricelist->sat_line ?? '',
                        'sat_description'  => $detail->pricelist->sat_description ?? '',
                        'quantity'         => $detail->quantity,
                        'unit_price'       => $detail->unit_price,
                        'subtotal'         => $detail->subtotal,
                        'unit_name'        => $detail->pricelist->unit->name ?? '',
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
    public function store(StoreQuoteWarehouseDetailRequest $request)
    {
        // Validación ya se realiza por el FormRequest

        try {
            $quoteWarehouse = \App\Models\QuoteWarehouse::findOrFail($request->input('quote_warehouse_id'));
            $quoteWarehouse->observations = $request->input('observations');
            $quoteWarehouse->save();

            return response()->json([
                'success' => true,
                'message' => 'Observaciones actualizadas correctamente.',
                'observations' => $quoteWarehouse->observations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar observaciones: ' . $e->getMessage(),
            ], 500);
        }
    }
}
