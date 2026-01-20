<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para manejar las operaciones CRUD de cotizaciones (Quotes).
 */
class QuoteController extends Controller
{
    /**
     * Muestra una lista paginada de cotizaciones.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quote::with(['employee', 'subClient', 'quoteCategory', 'quoteDetails']);

        if ($request->filled('q')) {
            $query->search($request->q);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('category')) {
            $query->where('quote_category_id', $request->category);
        }

        $quotes = $query->latest()->paginate(15);

        $quotes->getCollection()->transform(function ($quote) {
            $quote->total_amount = (float) $quote->quoteDetails->sum(function ($detail) {
                return $detail->quantity * $detail->unit_price;
            });
            return $quote;
        });

        return response()->json($quotes);
    }

    /**
     * Almacena una nueva cotización.
     *
     * @param StoreQuoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quote = Quote::create($request->validated());

        return response()->json($quote->load(['employee', 'subClient', 'quoteCategory']), 201);
    }

    /**
     * Muestra una cotización específica.
     *
     * @param Quote $quote
     * @return JsonResponse
     */
    public function show(Quote $quote): JsonResponse
    {
        return response()->json($quote->load(['employee', 'subClient', 'quoteCategory', 'details']));
    }

    /**
     * Actualiza una cotización específica.
     *
     * @param UpdateQuoteRequest $request
     * @param Quote $quote
     * @return JsonResponse
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): JsonResponse
    {
        $quote->update($request->validated());

        return response()->json($quote->load(['employee', 'subClient', 'quoteCategory']));
    }

    /**
     * Elimina una cotización específica.
     *
     * @param Quote $quote
     * @return JsonResponse
     */
    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();

        return response()->json(['message' => 'Cotización eliminada exitosamente']);
    }

    /**
     * Devuelve estadísticas de cotizaciones para el dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(): \Illuminate\Http\JsonResponse
    {
        $quotes = \App\Models\Quote::with('employee')->get();

        $totalQuotes = $quotes->count();
        $totalAmount = $quotes->sum(function ($quote) {
            return $quote->total_amount;
        });
        $approved = $quotes->where('status', 'APROBADO')->count();
        $pending = $quotes->where('status', 'POR HACER')->count();

        // Empleado más frecuente (a cargo)
        $mostEmployee = $quotes->groupBy('employee_id')->sortByDesc(function ($group) {
            return $group->count();
        })->first();
        $employee_fullname = $mostEmployee && $mostEmployee->first()->employee
            ? ($mostEmployee->first()->employee->fullname ?? $mostEmployee->first()->employee->name)
            : '';

        return response()->json([
            'total_quotes' => $totalQuotes,
            'total_amount' => $totalAmount,
            'approved' => $approved,
            'pending' => $pending,
            'employee_fullname' => $employee_fullname,
        ]);
    }

    /**
     * Muestra la vista previa de una cotización.
     *
     * @param \App\Models\Quote $quote
     * @return \Illuminate\View\View
     */
    public function preview(Quote $quote)
    {
        $quote->load(['employee', 'subClient', 'quoteCategory', 'quoteDetails']);
        $ceco = $quote->subClient->ceco ?? $quote->ceco ?? '----------';
        return view('filament.resources.quote-resource.pages.preview', [
            'numero_cotizacion' => $quote->request_number,
            // Priorizamos service_name si existe, sino usamos la categoría
            'servicio'          => $quote->service_name ?? $quote->quoteCategory->name ?? 'Sin servicio',
            'ruc_empresa'       => '20539249640', // Dato estático según tu diseño
            'empresa_nombre'    => 'SAT INDUSTRIALES',
            'cotizado_por'      => $quote->employee ? ($quote->employee->first_name . ' ' . $quote->employee->last_name) : 'No asignado',
            'n_solicitud'       => $quote->request_number,
            'cliente'           => $quote->subClient->name ?? 'Sin cliente',
            'jefe_energia'      => $quote->energy_sci_manager ?? '-',
            'fecha_cotizacion'  => $quote->quote_date ? $quote->quote_date->format('d/m/Y') : '-',
            'categoria'         => $quote->quoteCategory->name ?? '-',
            'ceco'    => $ceco,
            'fecha_ejecucion'   => $quote->execution_date ? $quote->execution_date->format('d/m/Y') : '-',
            'total_general'     => number_format($quote->total_amount, 2),
            'items'             => $quote->quoteDetails->map(function ($item, $idx) {
                return [
                    'tipo'        => 'item',
                    'index'       => $idx + 1,
                    'linea'       => $item->line ?? '-',
                    'descripcion' => $item->description,
                    'comentario'  => $item->comment,
                    'unidad'      => $item->unit_type ?? 'UND',
                    'cantidad'    => $item->quantity,
                    'pu'          => $item->unit_price,
                    'subtotal'    => $item->subtotal,
                ];
            }),
        ]);
    }
}
