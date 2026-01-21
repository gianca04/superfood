<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    @vite(['resources/css/app.css'])
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 overflow-x-auto min-h-screen" x-data="warehouseKanban()">
        @foreach($statuses as $statusKey => $statusLabel)
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div
                    class="p-4 mb-4 bg-white rounded-lg shadow-sm border-t-4 border-primary-500 dark:bg-gray-800 dark:border-primary-400">
                    <h3 class="font-bold text-lg text-gray-700 dark:text-gray-200">{{ $statusLabel }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $kanbanData[$statusKey]->count() }}
                        cotizaciones</span>
                </div>

                <!-- Column -->
                <div id="kanban-{{ $statusKey }}" data-status="{{ $statusKey }}"
                    class="kanban-column flex-1 p-4 bg-gray-50 rounded-xl space-y-4 min-h-[500px] border-2 border-dashed border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                    @foreach($kanbanData[$statusKey] as $quote)
                        <div data-id="{{ $quote->id }}"
                            class="bg-white p-4 rounded-lg shadow cursor-pointer hover:shadow-md transition-shadow border border-gray-100 dark:bg-gray-800 dark:border-gray-600">
                            <div class="flex justify-between items-start mb-2">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    #{{ $quote->id }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $quote->quote_date?->format('d/m/Y') }}</span>
                            </div>

                            <h4 class="font-semibold text-gray-800 mb-1 dark:text-gray-200">
                                {{ $quote->subClient->name ?? 'Sin Cliente' }}
                            </h4>

                            <p class="text-sm text-gray-600 line-clamp-2 mb-3 dark:text-gray-400">
                                {{ $quote->service_name ?? 'Sin servicio' }}
                            </p>

                            <div
                                class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <div class="text-xs text-gray-500">
                                    {{ $quote->user ? $quote->user->name : '' }}
                                </div>
                                @if($quote->quoteWarehouse && $quote->quoteWarehouse->attended_at)
                                    <div class="text-xs text-green-600 flex items-center"
                                        title="Atendido el {{ $quote->quoteWarehouse->attended_at }}">
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
                init() {
                    this.loadSortable();
                },
                loadSortable() {
                    if (typeof Sortable === 'undefined') {
                        const script = document.createElement('script');
                        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js';
                        script.onload = () => this.initSortable();
                        document.head.appendChild(script);
                    } else {
                        this.initSortable();
                    }
                },
                initSortable() {
                    const containers = document.querySelectorAll('.kanban-column');
                    containers.forEach(container => {
                        new Sortable(container, {
                            group: 'kanban',
                            animation: 150,
                            ghostClass: 'bg-primary-50',
                            dragClass: 'opacity-100',
                            delay: 0,
                            onEnd: (evt) => this.handleDrop(evt)
                        });
                    });
                },
                handleDrop(evt) {
                    if (evt.from === evt.to) return;

                    const itemEl = evt.item;
                    const newStatus = evt.to.getAttribute('data-status');
                    const quoteId = itemEl.getAttribute('data-id');

                    this.updateStatus(quoteId, newStatus);
                },
                updateStatus(quoteId, status) {
                    fetch('{{ route("warehouse.update-status") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ quoteId, status })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                new FilamentNotification()
                                    .title('Estado actualizado')
                                    .success()
                                    .duration(3000)
                                    .send();
                            } else {
                                this.showError();
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            this.showError();
                        });
                },
                showError() {
                    new FilamentNotification()
                        .title('Error al actualizar')
                        .body('Hubo un problema de conexión. Intente nuevamente.')
                        .danger()
                        .send();
                }
            }));
        });
    </script>
</x-filament-panels::page>