<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComplianceController extends Controller
{
    // Listar todas las actas de conformidad
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Compliance::with('project');
            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->has('state')) {
                $query->where('state', $request->state);
            }

            // Búsqueda general (Nombre cliente o Documento)
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('fullname_cliente', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                });
            }

            // --- PAGINACIÓN ---
            $compliances = $query->latest()->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'message' => 'Actas de conformidad obtenidas exitosamente',
                'data' => $compliances->items(),
                'pagination' => [
                    'total' => $compliances->total(),
                    'perPage' => $compliances->perPage(),
                    'currentPage' => $compliances->currentPage(),
                    'lastPage' => $compliances->lastPage(),
                    'from' => $compliances->firstItem(),
                    'to' => $compliances->lastItem(),
                    'hasMorePages' => $compliances->hasMorePages(),
                ],
                'meta' => [
                    'apiVersion' => '1.0',
                    'timestamp' => now()->utc()->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las actas: ' . $e->getMessage(),
                'meta' => [
                    'apiVersion' => '1.0',
                    'timestamp' => now()->utc()->toIso8601String(),
                ],
            ], 500);
        }
    }
    /**
     * Búsqueda rápida de actas de conformidad
     */
    public function quickSearch(Request $request): JsonResponse
    {
        try {
            // Validar que el parámetro query esté presente
            $request->validate([
                'query' => 'required|string|min:1|max:100'
            ]);

            $queryStr = $request->query('query');
            // Consultar actas cargando la relación project
            $compliances = Compliance::with('project')
                ->where(function ($q) use ($queryStr) {
                    // Buscar en datos del cliente (Acta)
                    $q->where('fullname_cliente', 'like', "%{$queryStr}%")
                        ->orWhere('document_number', 'like', "%{$queryStr}%")
                        // Buscar en el nombre del Proyecto (Relación)
                        ->orWhereHas('project', function ($pq) use ($queryStr) {
                            $pq->where('name', 'like', "%{$queryStr}%")
                                ->orWhere('service_code', 'like', "%{$queryStr}%");
                        });
                })
                ->latest() // Ordenar por las más recientes
                ->take(15) // Limitar resultados para búsqueda rápida
                ->get()
                ->map(function ($compliance) {
                    return [
                        'id' => $compliance->id,
                        'client_name' => $compliance->fullname_cliente ?? 'Sin nombre',
                        'document_number' => $compliance->document_number,
                        'project_name' => $compliance->project ? $compliance->project->name : 'Sin proyecto',
                        'state' => $compliance->state,
                        'created_at' => $compliance->created_at->format('d/m/Y'),
                    ];
                });

            return response()->json([
                "success" => true,
                "message" => "Búsqueda rápida de actas completada",
                "data" => $compliances,
                "meta" => [
                    "apiVersion" => "1.0",
                    "timestamp" => now()->utc()->toIso8601String()
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "success" => false,
                "message" => "Error de validación",
                "errors" => $e->errors(),
                "meta" => [
                    "apiVersion" => "1.0",
                    "timestamp" => now()->utc()->toIso8601String()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error en la búsqueda rápida de actas",
                "error_detail" => $e->getMessage(),
                "meta" => [
                    "apiVersion" => "1.0",
                    "timestamp" => now()->utc()->toIso8601String()
                ]
            ], 500);
        }
    }
    /**
     * Mostrar el detalle de una acta de conformidad con sus reportes.
     */
    public function show($id): JsonResponse
    {
        try {
            $compliance = Compliance::with([
                'project',
                'workReports.employee'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detalle del acta obtenido correctamente',
                'data' => $compliance,
                'meta' => [
                    'apiVersion' => '1.0',
                    'timestamp' => now()->utc()->toIso8601String(),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El acta de conformidad con ID ' . $id . ' no existe.',
                'meta' => [
                    'apiVersion' => '1.0',
                    'timestamp' => now()->utc()->toIso8601String(),
                ],
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle del acta: ' . $e->getMessage(),
                'meta' => [
                    'apiVersion' => '1.0',
                    'timestamp' => now()->utc()->toIso8601String(),
                ],
            ], 500);
        }
    }
}
