<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para manejar las operaciones de búsqueda de clientes.
 * Optimizado para selección rápida en formularios.
 */
class ClientController extends Controller
{
    /**
     * Muestra una lista paginada de clientes.
     * Opcionalmente filtra por búsqueda en business_name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quote::with(['employee', 'subClient', 'quoteCategory']);

        // Aplicar búsqueda mediante el scope definido en el modelo
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar por las más recientes
        $quotes = $query->latest()->paginate(12);

        return response()->json($quotes);
    }

    /**
     * Obtiene las estadísticas para los indicadores superiores.
     */
    public function getStatistics(): JsonResponse
    {
        // Calculamos el monto total sumando los subtotales de la relación details
        $totalAmount = DB::table('quote_details')->sum('subtotal');

        $stats = [
            'total_quotes' => Quote::count(),
            'total_amount' => (float) $totalAmount,
            'approved'     => Quote::where('status', 'APROBADO')->count(),
            'pending'      => Quote::where('status', 'POR HACER')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Busca clientes por business_name.
     * Optimizado para autocompletado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = Client::query();

        // Búsqueda solo en business_name
        if ($request->has('q') && $request->q) {
            $query->where('business_name', 'like', '%' . $request->q . '%');
        }

        // Limitar resultados para autocompletado
        $limit = min($request->get('limit', 10), 25);

        $clients = $query->select([
            'id',
            'business_name',
            'document_number'
        ])->limit($limit)->get();

        return response()->json($clients);
    }
}
