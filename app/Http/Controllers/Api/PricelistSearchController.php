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

        $pricelists = Pricelist::query()
            ->with(['unit', 'priceType'])
            ->when($priceTypeId, fn($q) => $q->where('price_type_id', $priceTypeId))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    // Search by code (sat_line) using prefix match
                    $subQuery->where('sat_line', 'LIKE', "{$query}%");

                    // Search by description using LIKE %term% for each word
                    // This allows partial matching like 'caja' finding 'MONTAJ_CAJAS'
                    $subQuery->orWhere(function ($descQuery) use ($query) {
                        $terms = array_filter(explode(' ', trim($query)));
                        foreach ($terms as $term) {
                            $descQuery->where('sat_description', 'LIKE', "%{$term}%");
                        }
                    });
                });
            })
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

    /**
     * Get initial items for each price type (first 15 of each).
     * Used when opening the search drawer.
     *
     * @return JsonResponse
     */
    public function initialItems(): JsonResponse
    {
        $priceTypes = \App\Models\PriceType::all();

        $result = [];

        foreach ($priceTypes as $priceType) {
            $items = Pricelist::query()
                ->with(['unit'])
                ->where('price_type_id', $priceType->id)
                ->limit(15)
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'code' => $item->sat_line,
                    'description' => $item->sat_description,
                    'unit' => $item->unit?->name ?? 'UND',
                    'unit_price' => (float) $item->unit_price,
                    'price_type_id' => $item->price_type_id,
                ]);

            $result[] = [
                'price_type' => [
                    'id' => $priceType->id,
                    'name' => $priceType->name,
                    'short_name' => explode(' ', $priceType->name)[0], // Primera palabra
                ],
                'items' => $items,
                'has_more' => Pricelist::where('price_type_id', $priceType->id)->count() > 15,
            ];
        }

        return response()->json($result);
    }

    /**
     * Get paginated items by price type.
     * Used for infinite scroll when a tab is selected.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byPriceType(Request $request): JsonResponse
    {
        $priceTypeId = $request->get('price_type_id');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 30);

        if (!$priceTypeId) {
            return response()->json(['error' => 'price_type_id is required'], 400);
        }

        $query = Pricelist::query()
            ->with(['unit'])
            ->where('price_type_id', $priceTypeId);

        $total = $query->count();

        $items = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->sat_line,
                'description' => $item->sat_description,
                'unit' => $item->unit?->name ?? 'UND',
                'unit_price' => (float) $item->unit_price,
                'price_type_id' => $item->price_type_id,
            ]);

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'has_more' => ($page * $perPage) < $total,
            ],
        ]);
    }
}
