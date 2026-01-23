<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Atención de Suministros - Tabla de Control</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <!-- Tailwind CSS with Plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Tailwind Config -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "primary-dark": "#0f62b6",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1a2632",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Custom scrollbar for better aesthetics in table */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Utility for input number no-spinner */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body
    class="flex flex-col min-h-screen antialiased bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display"
    x-data="{
        quoteWarehouseId: {{ $quoteWarehouse->id }},
        items: [
            @foreach ($details as $i => $item)
            {
                id: {{ $item['id'] ?? $i }},
                solicitado: {{ $item['quantity'] }},
                entregado: {{ $item['entregado'] ?? 0 }},
                despachar: {{ $item['a_despachar'] ?? 0 }}
            }, @endforeach
        ],
        observaciones: '{{ $quoteWarehouse->observations ?? '' }}',
        get progresoTotal() {
            let totalSolicitado = this.items.reduce((acc, item) => acc + item.solicitado, 0);
            let totalListo = this.items.reduce((acc, item) => acc + Math.min(item.entregado + item.despachar, item.solicitado), 0);
            return totalSolicitado === 0 ? 0 : Math.round((totalListo / totalSolicitado) * 100);
        },
        async enviarFormulario() {
            try {
                const response = await fetch('{{ route('quoteswarehouse.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        quote_warehouse_id: this.quoteWarehouseId,
                        observations: this.observaciones,
                        items: this.items
                    })
                });
                const data = await response.json();
                if (data.success) {
                    alert('¡Guardado con éxito!');
                } else {
                    alert('Error: ' + (data.message || 'No se pudo guardar'));
                }
            } catch (error) {
                alert('No se pudo conectar con el servidor.');
            }
        }
    }">
    <!-- Top Navigation -->

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden">
        <div class="mx-auto max-w-[1200px] p-6 lg:p-10 flex flex-col gap-8">
            <!-- Page Heading & Context -->
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-md ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-400">
                            {{ $quote->status ?? 'En Proceso' }}
                        </span>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ $quote->request_number ?? 'COT-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT) }}
                        </h1>
                    </div>
                    <div class="flex flex-wrap items-center text-sm gap-x-6 gap-y-2 text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">business</span>
                            <span>Cliente: <strong
                                    class="text-slate-700 dark:text-slate-300">{{ $client }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                            <span>
                                Fecha Cotización: {{ $quote->quote_date ? $quote->quote_date->format('d M Y') : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">event</span>
                            <span>
                                Fecha Ejecución:
                                {{ $quote->execution_date ? $quote->execution_date->format('d M Y') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Secondary Actions -->
                <div class="flex gap-3">
                    <button
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold border rounded-lg shadow-sm bg-surface-light dark:bg-surface-dark border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <span class="material-symbols-outlined mr-2 text-[20px]">print</span>
                        Imprimir
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold transition-colors rounded-lg bg-primary/10 text-primary hover:bg-primary/20"
                        @click="items.forEach(i => i.despachar = i.solicitado)">
                        <span class="material-symbols-outlined mr-2 text-[20px] fill-1">check_circle</span>
                        Atender Todo
                    </button>
                </div>
            </div>
            <!-- Table Container -->
            <div
                class="flex flex-col overflow-hidden border shadow-sm rounded-xl border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead
                            class="text-xs font-medium uppercase border-b bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="w-24 px-4 py-4" scope="col">LÍNEA SAT</th>
                                <th class="px-4 py-4 min-w-[300px]" scope="col">DESCRIPCIÓN ITEM</th>
                                <th class="w-20 px-4 py-4 text-center" scope="col">Unidad</th>
                                <th class="w-24 px-4 py-4 text-center" scope="col">Solicitado</th>
                                <th class="w-24 px-4 py-4 text-center" scope="col">Entregado</th>
                                <th class="w-40 px-4 py-4" scope="col">A Despachar</th>
                                <th class="w-20 px-4 py-4 text-right" scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($details as $i => $item)
                                <tr class="transition-colors group hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                    :class="items[{{ $i }}].entregado + items[{{ $i }}].despachar >=
                                        items[{{ $i }}].solicitado ? 'bg-slate-50/50 dark:bg-slate-800/30' :
                                        ''">
                                    <td :class="items[{{ $i }}].entregado + items[{{ $i }}].despachar >=
                                        items[{{ $i }}].solicitado ?
                                        'font-mono text-xs text-center align-middle text-slate-400 dark:text-slate-500 line-through underline' :
                                        'font-mono text-xs text-center align-middle text-slate-900 dark:text-white'"
                                        class="font-mono text-xs text-center align-middle text-slate-900 dark:text-white">
                                        {{ $item['sat_line'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 break-words whitespace-normal align-top">
                                        <div class="flex flex-col">
                                            <span
                                                :class="items[{{ $i }}].entregado + items[{{ $i }}]
                                                    .despachar >= items[{{ $i }}].solicitado ?
                                                    'line-through underline text-slate-400 dark:text-slate-500' :
                                                    'text-xs font-medium leading-relaxed text-slate-900 dark:text-white'"
                                                class="text-xs font-medium leading-relaxed text-slate-900 dark:text-white">
                                                {{ $item['sat_description'] ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center align-top">
                                        <span class="text-xs text-slate-400">{{ $item['unit_name'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center align-top">
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">
                                            {{ $item['quantity'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center align-top">
                                        <span class="font-semibold"
                                            :class="items[{{ $i }}].entregado + items[{{ $i }}]
                                                .despachar >= items[{{ $i }}].solicitado ?
                                                'text-green-600' : 'text-slate-900 dark:text-slate-200'">
                                            <span x-text="0"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="relative flex items-center">
                                            <input type="number" x-model.number="items[{{ $i }}].despachar"
                                                :max="items[{{ $i }}].solicitado" min="0"
                                                @input="if(items[{{ $i }}].despachar > items[{{ $i }}].solicitado) items[{{ $i }}].despachar = items[{{ $i }}].solicitado"
                                                class="block w-full rounded-md border-0 py-1.5 pl-2 pr-8 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary text-xs dark:bg-slate-900 dark:ring-slate-600 dark:text-white font-bold" />
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <span class="text-[10px] text-slate-400 uppercase">u.</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right align-top">
                                        <button
                                            @click="items[{{ $i }}].despachar = items[{{ $i }}].solicitado"
                                            class="inline-flex items-center justify-center p-1.5 transition-all rounded-full group/btn"
                                            :class="items[{{ $i }}].despachar >= items[{{ $i }}]
                                                .solicitado ?
                                                'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-500' :
                                                'text-slate-300 dark:text-slate-600 hover:text-green-600 dark:hover:text-green-500'"
                                            type="button" title="Marcar como listo">
                                            <span class="material-symbols-outlined text-[22px] group-hover/btn:fill-1"
                                                :class="items[{{ $i }}].despachar >= items[{{ $i }}]
                                                    .solicitado ?
                                                    'text-green-600 dark:text-green-500' : ''">check_circle</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Table Footer/Summary -->
            <div
                class="flex items-center justify-between px-6 py-3 text-sm border-t bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                <span>Mostrando {{ count($details) }} ítems</span>
                <div class="flex gap-2">
                    <span class="font-medium text-slate-700 dark:text-slate-300">Progreso Total:</span>
                    <span class="font-bold text-primary" x-text="progresoTotal + '%'"></span>
                </div>
            </div>
            <!-- NUEVO: Observaciones -->
            <div class="mt-6">
                <label for="warehouse-observations"
                    class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    Observaciones
                </label>
                <textarea id="warehouse-observations" name="warehouse_observations" rows="3" x-model="observaciones"
                    class="block w-full p-3 text-sm bg-white border rounded-lg resize-y border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary focus:border-primary"
                    placeholder="Ingrese aquí observaciones generales para el despacho..."></textarea>
            </div>
            <!-- Bottom Actions -->
            <div class="flex items-center justify-between pt-2">
                <div class="flex gap-4">
                    <button
                        class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-6 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 gap-2 text-base font-bold leading-normal transition-colors">
                        Cancelar
                    </button>
                    <!-- Botón Confirmar -->
                    <button type="button"
                        class="flex min-w-[200px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-6 bg-primary hover:bg-primary-dark text-white gap-2 pl-5 text-base font-bold leading-normal shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5"
                        @click="
                            const details = items.map(i => ({
                                a_despachar: i.despachar,
                                quantity: i.solicitado
                            }));
                            fetch('{{ route('quoteswarehouse.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({
                                    quote_warehouse_id: quoteWarehouseId,
                                    observations: observaciones ?? '',
                                    details: details
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if(data.success){
                                    alert('Observaciones guardadas correctamente');
                                } else {
                                    alert('Error: ' + (data.message || 'No se pudo guardar'));
                                }
                            });
                        ">
                        <span class="material-symbols-outlined text-[24px]">local_shipping</span>
                        <span class="truncate">Confirmar</span>
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
