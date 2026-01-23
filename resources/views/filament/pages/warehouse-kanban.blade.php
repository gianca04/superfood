<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <div class="grid min-h-screen grid-cols-1 gap-6 overflow-x-auto md:grid-cols-3" x-data="warehouseKanban()">
        <!-- Modal para previsualización -->
        <template x-teleport="body">
            <div x-show="showPreview" x-cloak
                class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div :class="isFullscreen ? 'fixed inset-0 w-screen h-screen max-w-none max-h-none rounded-none' :
                    'w-full max-w-6xl h-[92vh] rounded-xl'"
                    class="relative flex flex-col overflow-hidden transition-all duration-300 bg-white border border-gray-200 shadow-2xl dark:bg-gray-900 dark:border-gray-700"
                    @click.away="closePreview()">

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
        @foreach ($statuses as $statusKey => $statusLabel)
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="p-4 mb-4 bg-white rounded-lg dark:bg-gray-800 dark:border-primary-400">
                    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ $statusLabel }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $kanbanData[$statusKey]->count() }}
                        cotizaciones</span>
                </div>
                <!-- Column -->
                <div id="kanban-{{ $statusKey }}" data-status="{{ $statusKey }}"
                    class="kanban-column flex-1 p-4 bg-gray-50 rounded-xl space-y-4 min-h-[500px] border-2 border-dashed border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                    @foreach ($kanbanData[$statusKey] as $quoteWarehouse)
                        @php
                            $quote = $quoteWarehouse->quote;
                        @endphp
                        <div data-id="{{ $quoteWarehouse->id }}"
                            class="p-4 transition-shadow bg-white border border-gray-100 rounded-lg shadow cursor-pointer hover:shadow-md dark:bg-gray-800 dark:border-gray-600">
                            <div class="flex items-start justify-between mb-2">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    #{{ $quoteWarehouse->quote_id }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $quote?->quote_date?->format('d/m/Y') }}</span>
                            </div>

                            <button type="button"
                                @click="openPreview('{{ route('quoteswarehouse.preview', [$quoteWarehouse->id]) }}')"
                                class="block w-full px-4 py-2 mb-4 text-sm font-bold text-center text-white transition-all rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700">
                                PREVISUALIZAR PRUEBA
                            </button>

                            <h4 class="mb-1 font-semibold text-gray-800 dark:text-gray-200">
                                {{ $quote?->subClient?->name ?? 'Sin Cliente' }}
                            </h4>
                            <p class="mb-3 text-sm text-gray-600 line-clamp-2 dark:text-gray-400">
                                {{ $quote?->service_name ?? 'Sin servicio' }}
                            </p>
                            <div
                                class="flex items-center justify-between pt-2 mt-2 border-t border-gray-100 dark:border-gray-700">
                                <div class="text-xs text-gray-500">
                                    {{-- Aquí podrías mostrar el empleado de almacén si lo necesitas --}}
                                </div>
                                @if ($quoteWarehouse->attended_at)
                                    <div class="flex items-center text-xs text-green-600"
                                        title="Atendido el {{ $quoteWarehouse->attended_at }}">
                                        <x-heroicon-m-check-circle class="w-4 h-4 mr-1" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('warehouseKanban', () => ({
                isFullscreen: false,
                showPreview: false,
                previewUrl: '',

                init() {
                    this.loadSortable();
                    // Escuchar tecla ESC
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showPreview) this.closePreview();
                    });
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
                toggleFullscreen() { // <--- NUEVO
                    this.isFullscreen = !this.isFullscreen;
                },

                initSortable() {
                    const containers = document.querySelectorAll('.kanban-column');
                    containers.forEach(container => {
                        new Sortable(container, {
                            group: 'kanban',
                            animation: 150,
                            ghostClass: 'bg-primary-50',
                            onEnd: (evt) => this.handleDrop(evt)
                        });
                    });
                },

                handleDrop(evt) {
                    if (evt.from === evt.to) return;
                    const newStatus = evt.to.getAttribute('data-status');
                    const quoteId = evt.item.getAttribute('data-id');
                    this.updateStatus(quoteId, newStatus);
                },

                updateStatus(quoteId, status) {
                    fetch('{{ route('warehouse.update-status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                quoteId,
                                status
                            })
                        })
                        .then(res => res.json())
                        .then(data => { // Corregido: antes tenías 'then data =>' sin paréntesis
                            if (data.success) {
                                new FilamentNotification().title('Estado actualizado').success()
                                    .send();
                            } else {
                                new FilamentNotification().title('Error').danger().send();
                            }
                        })
                        .catch(err => console.error('Error en fetch:', err));
                }
            }));
        });
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-filament-panels::page>
