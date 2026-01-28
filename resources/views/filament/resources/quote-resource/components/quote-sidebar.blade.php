{{-- Quote Sidebar Component (Collapsible Top Panel) --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-sidebar') --}}

<div class="mb-4">
    {{-- Collapsible Header --}}
    <div @click="sidebarOpen = !sidebarOpen"
        class="flex items-center justify-between p-4 transition-all bg-white border border-gray-200 cursor-pointer dark:bg-gray-800 dark:border-gray-700 shadow-sm"
        :class="sidebarOpen ? 'rounded-t-2xl border-b-0' : 'rounded-2xl'">

        <div class="flex items-center gap-4">
            {{-- Avatar con iniciales --}}
            <div
                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-md shrink-0">
                {{ substr(auth()->user()->employee->full_name, 0, 1) . (strpos(auth()->user()->employee->full_name, ' ') !== false ? substr(auth()->user()->employee->full_name, strpos(auth()->user()->employee->full_name, ' ') + 1, 1) : '') }}
            </div>
            {{-- Info resumida --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Cotizador:</span>
                    <span class="ml-1 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        {{ auth()->user()->employee->full_name }}
                    </span>
                </div>
                {{-- Mostrar resumen cuando está colapsado --}}
                <template x-if="!sidebarOpen && (quote.client_id || quote.quote_category_id)">
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="hidden sm:block w-px h-4 bg-gray-300 dark:bg-gray-600"></span>
                        <span x-show="quote.quote_category_id"
                            class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400"
                                x-text="quoteCategories.find(c => c.id == quote.quote_category_id)?.name || ''"></span>
                        </span>
                        <span x-show="clientSearch" class="truncate max-w-[150px]">
                            <span x-text="clientSearch"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(isset($projectUrl) && $projectUrl)
                <a href="{{ $projectUrl }}"
                    class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                    <span class="text-sm material-symbols-outlined">arrow_forward</span>
                    Ir al Proyecto
                </a>
            @endif
            <span class="hidden sm:inline text-xs font-medium text-gray-400"
                x-text="sidebarOpen ? 'Ocultar' : 'Datos del proyecto'"></span>
            <span
                class="text-gray-400 transition-transform duration-300 material-symbols-outlined px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full"
                :class="sidebarOpen ? 'rotate-180' : ''">expand_more</span>
        </div>
    </div>

    {{-- Collapsible Content --}}
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-4 scale-[0.98]"
        class="overflow-visible bg-white border border-t-0 border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-b-2xl shadow-sm">

        <div class="p-5 sm:p-6">
            <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">

            {{-- N° Solicitud --}}
            <div class="mb-5">
                <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">N°
                    Solicitud</label>
                <div class="relative group">
                    <input class="text-sm sidebar-input bg-gray-50/50 font-black text-emerald-700 tracking-tight"
                        type="text" x-model="quote.request_number"
                        :value="quote.request_number || '{{ $suggestedRequestNumber ?? '' }}'" readonly />
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-gray-300 text-lg">lock</span>
                    </div>
                </div>
                <input type="hidden" name="request_number"
                    :value="quote.request_number || '{{ $suggestedRequestNumber ?? '' }}'">
                <input type="hidden" name="project_id" :value="quote.project_id || '{{ $suggestedProjectId ?? '' }}'">
            </div>

            {{-- Primera fila: Nombre del Servicio --}}
            <div class="mb-5">
                <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Nombre del
                    Servicio</label>
                <input x-model="quote.project_name" :readonly="!!projectFromPHP?.name"
                    :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': !!projectFromPHP?.name}"
                    class="text-sm sidebar-input shadow-inner focus:bg-white transition-all" type="text"
                    placeholder="Ej: Mantenimiento preventivo de equipos..." />
                <input type="hidden" name="project_name" x-model="quote.project_name">
            </div>

            {{-- Grid de campos en columnas --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

                {{-- Categoría --}}
                <div>
                    <label
                        class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Categoría</label>
                    <select x-model.number="quote.quote_category_id"
                        class="text-sm sidebar-input appearance-none bg-no-repeat bg-[right_0.5rem_center] cursor-pointer">
                        <option value="">Seleccionar...</option>
                        <template x-for="category in quoteCategories" :key="category.id">
                            <option :value="category.id" x-text="category.name"
                                :selected="category.id == quote.quote_category_id"></option>
                        </template>
                    </select>
                    <input type="hidden" name="quote_category_id" x-model="quote.quote_category_id">
                </div>

                {{-- Cliente --}}
                <div>
                    <label
                        class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Cliente</label>
                    <div class="searchable-select" @click.away="clientDropdownOpen = false">
                        <input type="text" x-model="clientSearch" @focus="clientDropdownOpen = true"
                            @click="clientDropdownOpen = true" @input="filterClients()"
                            :disabled="!!projectFromPHP?.sub_client_id" placeholder="Buscar..."
                            class="text-sm searchable-select-input shadow-inner" />
                        <div class="searchable-select-icon" :class="{ 'clickable': quote.client_id }">
                            <template x-if="quote.client_id && !projectFromPHP?.sub_client_id">
                                <button @click="clearClient()" type="button" class="p-1 hover:bg-gray-100 rounded-full">
                                    <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <template x-if="!quote.client_id">
                                <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </div>
                        <div x-show="clientDropdownOpen && filteredClients.length > 0" x-transition
                            class="searchable-select-dropdown">
                            <template x-for="client in filteredClients" :key="client.id">
                                <div @click="selectClientFromDropdown(client)"
                                    :class="{ 'selected': quote.client_id == client.id }"
                                    class="searchable-select-item">
                                    <div class="text-sm searchable-select-item-title" x-text="client.business_name">
                                    </div>
                                    <div class="searchable-select-item-subtitle"
                                        x-text="client.document_number || 'Sin RUC'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <input type="hidden" name="client_id" x-model="quote.client_id">
                </div>

                {{-- SubCliente / Tienda --}}
                <div>
                    <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Tienda /
                        SubCliente</label>
                    <div class="searchable-select" @click.away="subClientDropdownOpen = false">
                        <input type="text" x-model="subClientSearch" @focus="subClientDropdownOpen = true"
                            @click="subClientDropdownOpen = true" @input="filterSubClients()"
                            :disabled="!!projectFromPHP?.sub_client_id || !quote.client_id || loadingSubClients"
                            :placeholder="loadingSubClients ? 'Cargando...' : (!quote.client_id ? 'Primero cliente...' : 'Buscar...')"
                            class="text-sm searchable-select-input shadow-inner" />
                        <div class="searchable-select-icon" :class="{ 'clickable': quote.sub_client_id }">
                            <template x-if="quote.sub_client_id && !projectFromPHP?.sub_client_id">
                                <button @click="clearSubClient()" type="button"
                                    class="p-1 hover:bg-gray-100 rounded-full">
                                    <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <template x-if="!quote.sub_client_id && !loadingSubClients">
                                <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </div>
                        <div x-show="subClientDropdownOpen && filteredSubClients.length > 0" x-transition
                            class="searchable-select-dropdown">
                            <template x-for="subClient in filteredSubClients" :key="subClient.id">
                                <div @click="selectSubClientFromDropdown(subClient)"
                                    :class="{ 'selected': quote.sub_client_id == subClient.id }"
                                    class="searchable-select-item">
                                    <div class="text-sm searchable-select-item-title" x-text="subClient.name"></div>
                                    <div class="searchable-select-item-subtitle" x-text="subClient.ceco || 'Sin CECO'">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Gerente Energy SCI --}}
                <div class="md:col-span-1 lg:col-span-1">
                    <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Gerente
                        Energy SCI</label>
                    <input x-model="quote.energy_sci_manager"
                        class="text-sm sidebar-input shadow-inner focus:bg-white transition-all" type="text"
                        placeholder="Nombre..." />
                </div>

                {{-- CECO --}}
                <div class="md:col-span-1 lg:col-span-1">
                    <label
                        class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">CECO</label>
                    <input x-model="quote.ceco"
                        class="font-mono text-xs sidebar-input bg-gray-50/50 text-gray-400 font-bold" type="text"
                        readonly placeholder="Automático" />
                </div>
            </div>

            {{-- Segunda fila: Fechas y Estado --}}
            <div class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Fecha de Cotización --}}
                <div>
                    <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Fecha
                        Cotización</label>
                    <input x-model="quote.quote_date" class="text-sm sidebar-input cursor-pointer" type="date" />
                </div>

                {{-- Fecha de Ejecución --}}
                <div>
                    <label class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Fecha
                        Ejecución</label>
                    <input x-model="quote.execution_date" class="text-sm sidebar-input cursor-pointer" type="date" />
                </div>

                {{-- Estado --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <label
                        class="block mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Estado</label>
                    <select x-model="quote.status" class="text-sm sidebar-input font-bold" :class="{
                            'text-amber-600': quote.status == 'Pendiente',
                            'text-blue-600': quote.status == 'Enviado',
                            'text-emerald-600': quote.status == 'Aprobado',
                            'text-red-600': quote.status == 'Anulado'
                        }">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.effect(() => {
            // Log para depuración de datos iniciales
            console.log('[DEBUG] projectFromPHP:', window.projectFromPHP);
            console.log('[DEBUG] quote.client_id:', window.quote?.client_id);
            console.log('[DEBUG] clientSearch:', window.clientSearch);
            console.log('[DEBUG] quote.sub_client_id:', window.quote?.sub_client_id);
            console.log('[DEBUG] subClientSearch:', window.subClientSearch);
        });
    });
</script>