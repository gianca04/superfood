{{-- Quote Sidebar Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-sidebar') --}}

<aside class="lg:col-span-3 space-y-4">
    {{-- Provider Info --}}
    <div class="card-section p-5">
        <div
            class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
            <div class="font-bold text-gray-800 dark:text-white text-lg">SAT INDUSTRIALES</div>
            <div class="text-xs text-gray-500 font-mono">RUC: 20539249640</div>
            <div class="h-px bg-emerald-200/50 dark:bg-emerald-800/50 my-3"></div>
            <div class="flex items-center gap-2">
                <div
                    class="h-8 w-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">
                    JV
                </div>
                <div>
                    <div class="text-xs text-gray-400">Cotizado por</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Joel Viera</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Details --}}
    <div class="card-section p-5 space-y-4">
        <div class="text-xs uppercase tracking-wider text-gray-500 font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-500 text-base">description</span>
            Datos del Proyecto
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Servicio</label>
            <input x-model="quote.service_name" class="sidebar-input" placeholder="Nombre del servicio" type="text" />
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Cliente / Tienda</label>
            <input x-model="quote.client_name" class="sidebar-input font-semibold" type="text" />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Fecha Ejec.</label>
                <input x-model="quote.execution_date" class="sidebar-input" type="date" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">CECO</label>
                <input x-model="quote.ceco" class="sidebar-input font-mono" type="text" />
            </div>
        </div>
    </div>
</aside>