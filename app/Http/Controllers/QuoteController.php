<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\QuoteCategory;
use App\Models\QuoteWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $query = Quote::with(['employee', 'subClient.client', 'quoteCategory', 'quoteDetails', 'project']);

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

        // Filtro por rango de precios (total_amount)
        if ($request->filled('min_total')) {
            $query->whereHas('quoteDetails', function ($q) use ($request) {
                $q->selectRaw('quote_id, SUM(COALESCE(subtotal, quantity * unit_price)) as total')
                    ->groupBy('quote_id')
                    ->havingRaw('SUM(COALESCE(subtotal, quantity * unit_price)) >= ?', [$request->min_total]);
            });
        }
        if ($request->filled('max_total')) {
            $query->whereHas('quoteDetails', function ($q) use ($request) {
                $q->selectRaw('quote_id, SUM(COALESCE(subtotal, quantity * unit_price)) as total')
                    ->groupBy('quote_id')
                    ->havingRaw('SUM(COALESCE(subtotal, quantity * unit_price)) <= ?', [$request->max_total]);
            });
        }

        $quotes = $query->latest()->paginate(15);

        $quotes->getCollection()->transform(function ($quote) {
            $quote->total_amount = (float) $quote->quoteDetails->sum(function ($detail) {
                return $detail->subtotal ?? ($detail->quantity * $detail->unit_price);
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
        // 1. Obtener datos validados
        $validated = $request->validated();

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
                // 2. Crear la Cotización
                // Aseguramos que el status sea 'POR HACER' si no se envía
                $validated['status'] = $validated['status'] ?? 'Pendiente';

                // Asignar el employee_id del usuario autenticado
                if (Auth::check() && Auth::user()->employee) {
                    $validated['employee_id'] = Auth::user()->employee->id;
                }

                // Guardar project_id si viene en el request
                if ($request->has('project_id')) {
                    $validated['project_id'] = $request->input('project_id');
                }

                // Crear la cotización (y el proyecto si es necesario)
                $quote = Quote::createWithProject($validated);

                // 3. Procesar los detalles (items)
                $items = $request->input('items', []);
                $line = 1; // Inicializar contador de línea
                foreach ($items as $item) {
                    // Cálculo backend del subtotal
                    $quantity = (float) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $subtotal = round($quantity * $unitPrice, 2);

                    $quote->details()->create([
                        'pricelist_id' => $item['pricelist_id'],
                        'item_type' => $item['item_type'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'comment' => $item['comment'] ?? null,
                        'line' => $line++, // Asignar línea incremental
                    ]);
                }



                // Actualizar service_start_date del proyecto con execution_date de la cotización
                if ($quote->project && $quote->execution_date) {
                    $quote->project->update([
                        'service_start_date' => $quote->execution_date
                    ]);
                }

                // Crear registro en quote_warehouse si el status es 'Aprobado' o 'APROBADO'
                if (
                    isset($validated['status']) &&
                    (strtolower($validated['status']) === 'aprobado')
                ) {
                    $exists = QuoteWarehouse::where('quote_id', $quote->id)->exists();
                    if (!$exists) {
                        QuoteWarehouse::create([
                            'quote_id'    => $quote->id,
                            'employee_id' => Auth::user()->employee->id ?? null,
                            'status'      => 'Pendiente',
                            'observations' => null,
                        ]);
                    }
                }

                // Notificación SweetAlert para creación exitosa
                session()->flash('swal', [
                    'title' => '¡Cotización creada!',
                    'text' => 'La cotización se ha guardado correctamente.',
                    'icon' => 'success',
                    'timer' => 2000,
                    'showConfirmButton' => false,
                ]);

                return response()->json($quote->load(['employee', 'subClient', 'quoteCategory', 'details', 'project']), 201);
            });
        } catch (\Exception $e) {
            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'Error al guardar la cotización: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
            return response()->json([
                'message' => 'Error al guardar la cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function categories()
    {
        $categories = QuoteCategory::orderBy('name')->get(['id', 'name']);
        return response()->json($categories);
    }

    /**
     * Muestra una cotización específica.
     *
     * @param Quote $quote
     * @return JsonResponse
     */
    public function show(Quote $quote): JsonResponse
    {
        return response()->json($quote->load(['employee', 'subClient', 'quoteCategory', 'details', 'project']));
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
        $validated = $request->validated();

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request, $quote) {
                // Si no se envía employee_id, mantener el original o asignar el del usuario autenticado
                if (!isset($validated['employee_id']) || $validated['employee_id'] === null) {
                    if ($quote->employee_id) {
                        $validated['employee_id'] = $quote->employee_id;
                    } elseif (Auth::check() && Auth::user()->employee) {
                        $validated['employee_id'] = Auth::user()->employee->id;
                    }
                }

                // Mantener project_id si no se envía
                if (!isset($validated['project_id']) || $validated['project_id'] === null) {
                    $validated['project_id'] = $quote->project_id;
                }

                // Actualizar la cotización
                $quote->update($validated);

                // Actualizar nombre del proyecto si se envía
                if (isset($validated['project_name']) && $quote->project) {
                    $quote->project->update(['name' => $validated['project_name']]);
                }

                // Actualizar service_start_date del proyecto con execution_date de la cotización
                if ($quote->project && $quote->execution_date) {
                    $quote->project->update([
                        'service_start_date' => $quote->execution_date
                    ]);
                }

                // Si se envían items, actualizar los detalles
                if ($request->has('items')) {
                    // Eliminar detalles existentes
                    $quote->details()->delete();

                    // Crear nuevos detalles
                    $items = $request->input('items', []);
                    $line = 1; // Inicializar contador de línea
                    foreach ($items as $item) {
                        $quantity = (float) $item['quantity'];
                        $unitPrice = (float) $item['unit_price'];
                        $subtotal = round($quantity * $unitPrice, 2);

                        $quote->details()->create([
                            'pricelist_id' => $item['pricelist_id'],
                            'item_type' => $item['item_type'],
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                            'comment' => $item['comment'] ?? null,
                            'line' => $line++, // Asignar línea incremental
                        ]);
                    }
                }

                // Crear registro en quote_warehouse si el status es 'Aprobado' o 'APROBADO'
                if (
                    isset($validated['status']) &&
                    (strtolower($validated['status']) === 'aprobado')
                ) {
                    $exists = QuoteWarehouse::where('quote_id', $quote->id)->exists();
                    if (!$exists) {
                        QuoteWarehouse::create([
                            'quote_id'    => $quote->id,
                            'employee_id' => Auth::user()->employee->id ?? null,
                            'status'      => 'Pendiente',
                            'observations' => null,
                        ]);
                    }
                }

                session()->flash('swal', [
                    'title' => '¡Cotización actualizada!',
                    'text' => 'La cotización se ha actualizado correctamente.',
                    'icon' => 'success',
                    'timer' => 2000,
                    'showConfirmButton' => false,
                ]);
                return response()->json($quote->load(['employee', 'subClient', 'quoteCategory', 'details', 'project']));
            });
        } catch (\Exception $e) {
            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'Error al actualizar la cotización: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
            return response()->json([
                'message' => 'Error al actualizar la cotización',
                'error' => $e->getMessage()
            ], 500);
        }
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
        session()->flash('swal', [
            'title' => '¡Cotización eliminada!',
            'text' => 'La cotización se ha eliminado correctamente.',
            'icon' => 'success',
            'timer' => 2000,
            'showConfirmButton' => false,
        ]);
        return response()->json(['message' => 'Cotización eliminada exitosamente']);
    }

    /**
     * Devuelve estadísticas de cotizaciones para el dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        $quotes = \App\Models\Quote::with('employee')->get();

        $totalQuotes = $quotes->count();
        $totalAmount = $quotes->sum(function ($quote) {
            return $quote->total_amount;
        });
        $approved = $quotes->where('status', 'Aprobado')->count();
        $pending = $quotes->where('status', 'Pendiente')->count();

        // Cotizadores únicos para el filtro
        $employees = $quotes->pluck('employee')->filter()->unique('id')->map(function ($emp) {
            return [
                'id' => $emp->id,
                'fullname' => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
            ];
        })->values();

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
            'employees' => $employees,
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
        $quote->load([
            'employee',
            'subClient',
            'quoteCategory',
            'quoteDetails.pricelist.unit',
            'project',
        ]);
        $ceco = $quote->subClient->ceco ?? $quote->ceco ?? '----------';
        // 1. Agrupamos los detalles por el tipo de ítem
        $groupedDetails = $quote->quoteDetails->groupBy('item_type');
        $formattedId = str_pad($quote->id, 5, '0', STR_PAD_LEFT);
        $sections = [
            'VIATICOS'   => 'VIATICOS',
            'SUMINISTRO' => 'SUMINISTRO',
            'MANO DE OBRA' => 'MANO DE OBRA',
            'SERVICIO'   => 'SERVICIO'
        ];
        $itemsData = collect();
        $sectionIndex = 1;

        foreach ($sections as $type => $label) {
            if ($groupedDetails->has($type)) {
                // Añadimos fila de encabezado de sección
                $itemsData->push([
                    'tipo' => 'header',
                    'numero' => $sectionIndex++,
                    'nombre' => $label
                ]);

                // Ordenar los detalles por 'line' antes de añadirlos
                $sortedDetails = $groupedDetails->get($type)->sortBy('line');

                // Añadimos los ítems de esta sección ordenados por line
                foreach ($sortedDetails as $detail) {
                    $itemsData->push([
                        'tipo'        => 'item',
                        'line'        => $detail->line,  // Añadido para pasar el número de línea
                        'linea'       => $detail->pricelist->sat_line ?? '-',
                        'descripcion' => $detail->pricelist->sat_description ?? 'Sin descripción',
                        'comentario'  => $detail->comment ?? '-',
                        'unidad'      => $detail->pricelist->unit->name ?? 'UND',
                        'cantidad'    => $detail->quantity,
                        'pu'          => $detail->unit_price,
                        'subtotal'    => $detail->subtotal,
                    ]);
                }
            }
        }
        return view('filament.resources.quote-resource.pages.preview', [
            'original_id'       => $quote->id,
            'quote_id'          => $formattedId,
            'numero_cotizacion' => $quote->request_number ?? '-',
            'servicio'          => $quote->project->name ?? $quote->quoteCategory->name ?? 'Sin servicio',
            'ruc_empresa'       => '20539249640',
            'empresa_nombre'    => 'SAT INDUSTRIALES',
            'cotizado_por'      => $quote->employee ? $quote->employee->short_name : 'No asignado',
            'n_solicitud'       => $quote->project && $quote->project->request_number ? $quote->project->request_number : '-',  // Ajustado para mostrar '-' si no hay request_number
            'cliente'           => $quote->subClient->name ?? 'Sin cliente',
            'jefe_energia'      => $quote->energy_sci_manager ?? '-',
            'fecha_cotizacion'  => $quote->quote_date ? $quote->quote_date->format('d/m/Y') : '-',
            'categoria'         => $quote->quoteCategory->name ?? '-',
            'ceco'              => $ceco,
            'fecha_ejecucion'   => $quote->execution_date ? $quote->execution_date->format('d/m/Y') : '-',
            'total_general'     => number_format($quote->total_amount, 2),
            'items'             => $itemsData,
            // 'categories'   => QuoteCategory::select('id', 'name')->get(),
        ]);
    }
    public function getStats()
    {
        $totalQuotes = Quote::count();
        $totalAmount = Quote::sum('total_amount');
        $approvedCount = Quote::where('status', 'Aprobado')->count();
        $pendingCount = Quote::where('status', 'Pendiente')->count();

        return response()->json([
            'total_quotes' => $totalQuotes,
            'total_amount' => $totalAmount,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
        ]);
    }
}
