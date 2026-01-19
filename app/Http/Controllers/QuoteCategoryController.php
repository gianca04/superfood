<?php

namespace App\Http\Controllers;

use App\Models\QuoteCategory;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para manejar las operaciones de categorías de cotización.
 * Optimizado para selects y listados pequeños.
 */
class QuoteCategoryController extends Controller
{
    /**
     * Obtiene todas las categorías de cotización.
     * Optimizado para selects ya que generalmente son pocos registros.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = QuoteCategory::select(['id', 'name', 'description'])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
