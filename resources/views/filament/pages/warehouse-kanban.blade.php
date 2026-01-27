<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <div x-data="warehouseKanban()">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($records as $quoteWarehouse)
                @php
                    $quote = $quoteWarehouse->quote;
                    $progress = (float) $quoteWarehouse->progress; // Aseguramos que sea un número

                    // 1. Definimos el color base según tus nuevos rangos
                    $colorName = match (true) {
                        $progress <= 40 => 'red',
                        $progress <= 80 => 'yellow',
                        $progress > 80 => 'green',
                        default => 'gray',
                    };

                    // 2. Definimos el color del status (opcional si lo usas en otra parte)
                    $statusColor = match (strtolower($quoteWarehouse->status)) {
                        'atendido' => 'emerald',
                        'parcial' => 'yellow',
                        'pendiente' => 'red',
                        default => 'gray',
                    };

                    // Lógica de ítems atendidos (se mantiene igual)
                    $itemsAtendidos = $quote->quoteDetails
                        ->filter(function ($detail) use ($quoteWarehouse) {
                            $attendedQuantity = $quoteWarehouse
                                ->details()
                                ->where('quote_detail_id', $detail->id)
                                ->sum('attended_quantity');
                            return $attendedQuantity >= $detail->quantity;
                        })
                        ->count();
                @endphp

                <div
                    class="group relative bg-white dark:bg-[#1a2634] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 p-4 flex flex-col gap-3 h-auto">
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 dark:bg-{{ $statusColor }}-900/30 dark:text-{{ $statusColor }}-400">
                            <span class="size-2 rounded-full bg-{{ $statusColor }}-500"></span>
                            {{ ucfirst($quoteWarehouse->status) }}
                        </span>
                        <span class="font-mono text-xs text-slate-400">#{{ $quoteWarehouse->quote_id }}</span>
                    </div>

                    <div class="flex flex-col">
                        <h3 class="text-base font-bold leading-tight truncate text-slate-900 dark:text-white">
                            {{ $quote?->subClient?->name ?? 'Sin Cliente' }}
                        </h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $quote?->quote_date?->format('d/m/Y') ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Items atendidos</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $itemsAtendidos }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Total Items</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $quote->quoteDetails->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 mt-auto">
                        {{-- Barra de progreso --}}
                        <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            {{-- Usamos la variable $colorName para construir la clase bg-color-500 --}}
                            <div class="h-full bg-{{ $colorName }}-500 rounded-full"
                                style="width: {{ $progress }}%">
                            </div>
                        </div>

                        <div class="flex justify-between text-[10px] font-bold">
                            {{-- Texto del porcentaje con el mismo color --}}
                            <span class="text-{{ $colorName }}-600">{{ $progress }}%</span>
                            <span class="uppercase text-slate-400">Progreso</span>
                        </div>
                    </div>

                    <!-- Botón de imprimir (abrir en nueva pestaña) -->
                    <a href="{{ route('quoteswarehouse.pdf', $quoteWarehouse->id) }}" target="_blank"
                        class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold border rounded-lg shadow-sm bg-surface-light dark:bg-surface-dark border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        <span class="material-symbols-outlined mr-2 text-[20px]">print</span>
                        Imprimir
                    </a>

                    <button type="button"
                        @click.prevent="openPreview('{{ route('quoteswarehouse.preview', [$quoteWarehouse->id]) }}')"
                        class="block w-full px-4 py-2 text-xs font-black tracking-widest text-center text-white uppercase transition-all rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700">
                        Atender
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="mt-8">
            <div class="flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm" aria-label="Pagination">
                    {{ $records->links('pagination::tailwind') }}
                </nav>
            </div>
        </div>

        <template x-teleport="body">
            <!-- Modal para previsualización -->
            <div x-show="showPreview" x-cloak
                class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div :class="isFullscreen ? 'fixed inset-0 w-screen h-screen max-w-none max-h-none rounded-none' :
                    'w-full max-w-6xl h-[92vh] rounded-xl'"
                    class="relative flex flex-col overflow-hidden transition-all duration-300 bg-white border border-gray-200 shadow-2xl dark:bg-gray-900 dark:border-gray-700"
                    {{-- @click.away="closePreview()" --}} {{-- Eliminado para que no se cierre al hacer clic fuera --}}>

                    <div
                        class="flex items-center justify-between p-4 border-b dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
                        <!-- Botón pantalla completa -->
                        <div class="flex gap-2">
                            <button @click="toggleFullscreen()"
                                class="p-2 transition-colors rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                                title="Pantalla completa">
                                <span class="text-gray-500 material-symbols-outlined"
                                    x-text="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"></span>
                            </button>
                        </div>
                        <!-- Header centrado y estirado -->
                        <header
                            class="flex items-center justify-center flex-1 px-10 py-3 border-b-0 border-solid shadow-none whitespace-nowrap">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center rounded size-8 bg-primary/10 text-primary">
                                    <span class="text-2xl material-symbols-outlined">warehouse</span>
                                </div>
                                <h2
                                    class="text-lg font-bold leading-tight tracking-[-0.015em] text-slate-900 dark:text-white">
                                    Gestión de Almacén
                                </h2>
                            </div>
                        </header>
                        <!-- Botón cerrar -->
                        <button @click="closePreview()"
                            class="p-2 transition-colors rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                            title="Cerrar">
                            <span class="text-gray-500 material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="flex-1 bg-white">
                        <template x-if="showPreview">
                            <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('warehouseKanban', () => ({
                isFullscreen: false,
                showPreview: false,
                previewUrl: '',

                init() {
                    this.loadSortable();
                },

                openPreview(url) {
                    this.previewUrl = url;
                    this.showPreview = true;
                    document.body.style.overflow = 'hidden'; // Bloquear scroll
                },

                closePreview() {
                    this.showPreview = false;
                    this.previewUrl = '';
                    this.isFullscreen = false;
                    document.body.style.overflow = 'auto'; // Habilitar scroll
                },

                loadSortable() {
                    if (typeof Sortable === 'undefined') {
                        const script = document.createElement('script');
                        script.src =
                            'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js';
                        script.onload = () => this.initSortable();
                        document.head.appendChild(script);
                    } else {
                        this.initSortable();
                    }
                },
                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                },
            }));
        });
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-filament-panels::page>
