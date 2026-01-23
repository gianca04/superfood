<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

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
    //Buscar por descripcion y sat_line

}
