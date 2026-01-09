@if ($project)
    <div class="p-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Nombre del Proyecto</h3>
                <p class="text-sm text-gray-900">{{ $project->name }}</p>
            </div>
        </div>

        <hr class="border-gray-200">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Razón Social</h3>
                <p class="text-sm text-gray-900">{{ $project->subClient?->client?->business_name ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">RUC</h3>
                <p class="text-sm text-gray-900">{{ $project->subClient?->client?->document_number ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Sede / Tienda</h3>
                <p class="text-sm text-gray-900">{{ $project->subClient?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Dirección</h3>
                <p class="text-sm text-gray-900">{{ $project->subClient?->address ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="p-4">
            <div class="flex items-center gap-2 pb-2 mb-4 text-gray-600 border-b dark:text-gray-400">
                <x-heroicon-o-calendar class="w-5 h-5" />
                <h3 class="text-sm font-bold tracking-wider uppercase">Periodo del Proyecto</h3>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-xs italic font-medium text-gray-500">Fecha de Inicio</span>
                    <div
                        class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                        <x-heroicon-o-calendar class="w-4 h-4 text-primary-500" />
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $project->service_start_date ? $project->service_start_date->format('d/m/Y') : 'No definida' }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs italic font-medium text-gray-500">Fecha de Fin</span>
                    <div
                        class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                        <x-heroicon-o-calendar-days class="w-4 h-4 text-danger-500" />
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $project->service_end_date ? $project->service_end_date->format('d/m/Y') : 'No definida' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <p class="p-4 text-center">No se encontró información del proyecto.</p>
@endif
