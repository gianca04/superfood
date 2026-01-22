<?php

namespace App\Http\Controllers;

use App\Models\SubClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para manejar las operaciones de búsqueda de subclientes.
 */
class SubClientController extends Controller
{
    /**
     * Muestra una lista paginada de subclientes.
     * Opcionalmente filtra por client_id y búsqueda solo en name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubClient::query();

        // Filtro opcional por cliente
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Búsqueda por nombre o ceco
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('ceco', 'like', "%$q%");
            });
        }

        // Paginación (por defecto 30)
        $perPage = $request->input('per_page', 30);
        $subClients = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $subClients->items(),
            'meta' => [
                'has_more' => $subClients->hasMorePages(),
                'current_page' => $subClients->currentPage(),
                'per_page' => $subClients->perPage(),
                'total' => $subClients->total(),
            ],
        ]);
    }

    /**
     * Busca subclientes por name.
     * Optimizado para autocompletado con filtro obligatorio por client_id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        // Filtro requerido por cliente (para optimizar la búsqueda)
        if (!$request->has('client_id') || !$request->client_id) {
            return response()->json(['error' => 'client_id es requerido para la búsqueda'], 400);
        }

        $query = SubClient::where('client_id', $request->client_id);

        // Búsqueda solo en name
        if ($request->has('q') && $request->q) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // No limitar resultados para devolver todos los subclientes
        $subClients = $query->select([
            'id',
            'name',
            'ceco'
        ])->get();

        return response()->json($subClients);
    }

    /**
     * Muestra un subcliente específico por su ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $subClient = SubClient::findOrFail($id);
        return response()->json($subClient);
    }
}
