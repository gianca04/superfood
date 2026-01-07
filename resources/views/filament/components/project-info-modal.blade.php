<div class="p-2 space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        <div class="space-y-4">
            <h4 class="pb-1 text-xs font-bold tracking-widest text-gray-400 uppercase border-b">Identificación</h4>

            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                    <x-heroicon-o-briefcase class="w-5 h-5 text-primary-600" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Proyecto</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $project->name }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <x-heroicon-o-building-office-2 class="w-5 h-5 text-gray-600" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Cliente / Sede</p>
                    <p class="text-sm text-gray-900 dark:text-gray-200">
                        {{ $project->subClient->client->business_name ?? 'N/A' }}
                        <span class="mx-1 text-gray-400">|</span>
                        <span class="font-medium">{{ $project->subClient->name ?? 'N/A' }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h4 class="pb-1 text-xs font-bold tracking-widest text-gray-400 uppercase border-b">Cronograma</h4>

            <div
                class="flex items-center gap-4 p-3 border border-gray-100 bg-gray-50 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                <div class="flex-1 text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Inicio</p>
                    <p class="text-sm font-semibold text-primary-600">
                        {{ $project->start_date?->format('d/m/Y') ?? '---' }}</p>
                </div>
                <div class="text-gray-300">
                    <x-heroicon-m-chevron-right class="w-5 h-5" />
                </div>
                <div class="flex-1 text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Finalización</p>
                    <p class="text-sm font-semibold text-danger-600">{{ $project->end_date?->format('d/m/Y') ?? '---' }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3 px-1">
                <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400" />
                <div>
                    <p class="text-xs font-medium text-gray-500">Ubicación</p>
                    <p class="text-sm text-gray-900 dark:text-gray-300">
                        {{ $project->location['location'] ?? 'No especificada' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div
        class="p-4 border border-gray-200 border-dashed bg-gray-50 dark:bg-gray-900/50 rounded-xl dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex items-center gap-3">
                <x-heroicon-m-phone class="w-4 h-4 text-gray-400" />
                <span
                    class="text-sm text-gray-600 dark:text-gray-400">{{ $project->contact_phone ?? 'Sin teléfono' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <x-heroicon-m-envelope class="w-4 h-4 text-gray-400" />
                <span
                    class="text-sm text-gray-600 dark:text-gray-400">{{ $project->contact_email ?? 'Sin correo' }}</span>
            </div>
        </div>
    </div>

    @if ($project->description)
        <div class="pt-2">
            <p class="mb-2 text-xs font-bold tracking-widest text-gray-400 uppercase">Descripción del Proyecto</p>
            <div
                class="p-3 text-sm leading-relaxed text-gray-700 bg-white border rounded-lg dark:text-gray-300 dark:bg-gray-800">
                {{ $project->description }}
            </div>
        </div>
    @endif
</div>
