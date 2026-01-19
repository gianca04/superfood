<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pricelist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PricelistSearchController extends Controller
{
    /**
     * Search pricelists by code or description.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $priceTypeId = $request->get('price_type_id');
        $limit = $request->get('limit', 15);

        $pricelists = Pricelist::query()
            ->with(['unit', 'priceType'])
            ->when($priceTypeId, fn($q) => $q->where('price_type_id', $priceTypeId))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('sat_line', 'LIKE', "%{$query}%")
                        ->orWhere('sat_description', 'LIKE', "%{$query}%");
                });
            })
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->sat_line,
                'description' => $item->sat_description,
                'unit' => $item->unit?->name ?? 'UND',
                'unit_price' => (float) $item->unit_price,
                'price_type' => $item->priceType?->name,
            ]);

        return response()->json($pricelists);
    }

    /**
     * Get price types for dropdown.
     * 
     * @return JsonResponse
     */
    public function priceTypes(): JsonResponse
    {
        $priceTypes = \App\Models\PriceType::all(['id', 'name']);
        return response()->json($priceTypes);
    }
}
