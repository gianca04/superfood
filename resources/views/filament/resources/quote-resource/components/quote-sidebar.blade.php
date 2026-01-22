{{-- Quote Sidebar Component (Collapsible Top Panel) --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-sidebar') --}}

<div class="mb-4">
    {{-- Collapsible Header --}}
    <div @click="sidebarOpen = !sidebarOpen"
        class="flex items-center justify-between p-3 transition-all bg-white border border-gray-200 cursor-pointer dark:bg-gray-800 dark:border-gray-700"
        :class="sidebarOpen ? 'rounded-t-xl border-b-0' : 'rounded-xl'">

        <div class="flex items-center gap-3">
            {{-- Avatar con iniciales --}}
            <div
                class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full bg-emerald-500 shrink-0">
                {{ substr(auth()->user()->employee->full_name, 0, 1) . (strpos(auth()->user()->employee->full_name, ' ') !== false ? substr(auth()->user()->employee->full_name, strpos(auth()->user()->employee->full_name, ' ') + 1, 1) : '') }}
            </div>
            {{-- Info resumida --}}
            <div class="flex items-center gap-4">
                <div>
                    <span class="text-xs text-gray-400">Cotizador:</span>
                    <span class="ml-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ auth()->user()->employee->full_name }}
                    </span>
                </div>
                {{-- Mostrar resumen cuando está colapsado --}}
                <template x-if="!sidebarOpen && (quote.client_id || quote.quote_category_id)">
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-px h-4 bg-gray-300 dark:bg-gray-600"></span>
                        <span x-show="quote.quote_category_id">
                            <span class="font-medium text-emerald-600 dark:text-emerald-400"
                                x-text="quoteCategories.find(c => c.id == quote.quote_category_id)?.name || ''"></span>
                        </span>
                        <span x-show="clientSearch">
                            <span x-text="clientSearch"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400" x-text="sidebarOpen ? 'Ocultar' : 'Datos del proyecto'"></span>
            <span class="text-gray-400 transition-transform duration-200 material-symbols-outlined"
                :class="sidebarOpen ? 'rotate-180' : ''">expand_more</span>
        </div>
    </div>

    {{-- Collapsible Content --}}
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="overflow-visible bg-white border border-t-0 border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-b-xl">

        <div class="p-4 pb-6">
            <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">


            {{-- N° Solicitud --}}
            <div class="mb-4">
                <label class="block mb-1 text-xs font-medium text-gray-500">N° Solicitud</label>
                <input class="text-sm sidebar-input bg-gray-50 font-bold text-emerald-700" type="text"
                    x-model="quote.request_number"
                    :value="quote.request_number || '{{ $suggestedRequestNumber ?? '' }}'" readonly />
                {{-- Campo oculto para el POST --}}
                <input type="hidden" name="request_number"
                    :value="quote.request_number || '{{ $suggestedRequestNumber ?? '' }}'">
                <input type="hidden" name="project_id" :value="quote.project_id || '{{ $suggestedProjectId ?? '' }}'">
                {{-- Mostrar el project_id en la vista para depuración/visualización --}}
                <div class="mt-1 text-xs text-gray-400">
                    <span>Project ID:</span>
                    <span x-text="quote.project_id || '{{ $suggestedProjectId ?? '' }}'"></span>
                </div>
            </div>
            {{-- Primera fila: Nombre del Servicio (campo amplio) --}}
            <div class="mb-4">
                <label class="block mb-1 text-xs font-medium text-gray-500">Nombre del Servicio</label>
                <input x-model="quote.service_name" class="text-sm sidebar-input" type="text"
                    placeholder="Ej: Mantenimiento preventivo de equipos..." />
                <input type="hidden" name="service_name" x-model="quote.service_name">
            </div>
            {{-- -aca colocar el project_id para mandar a la base de datos con un dehydrated --}}
            <input type="hidden" name="request_number" :value="quote.request_number">
            <input type="hidden" name="project_id" :value="quote.project_id">
            {{-- Grid de campos en columnas para optimizar espacio --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">

                {{-- Categoría --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Categoría</label>
                    <select x-model.number="quote.quote_category_id" class="text-sm sidebar-input">
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
                    <label class="block mb-1 text-xs font-medium text-gray-500">Cliente</label>
                    <div class="searchable-select" @click.away="clientDropdownOpen = false">
                        <input type="text" x-model="clientSearch" @focus="clientDropdownOpen = true"
                            @click="clientDropdownOpen = true" @input="filterClients()"
                            :disabled="!!projectFromPHP?.sub_client_id" placeholder="Buscar..."
                            class="text-sm searchable-select-input" />
                        <div class="searchable-select-icon" :class="{ 'clickable': quote.client_id }">
                            <template x-if="quote.client_id && !projectFromPHP?.sub_client_id">
                                <button @click="clearClient()" type="button">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <template x-if="!quote.client_id">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div x-show="clientDropdownOpen && clientSearch.length > 0 && filteredClients.length === 0"
                            class="text-sm searchable-select-dropdown searchable-select-empty">
                            No se encontraron resultados
                        </div>
                    </div>
                    <input type="hidden" name="client_id" x-model="quote.client_id">
                </div>

                {{-- SubCliente / Tienda --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Tienda / SubCliente</label>
                    <div class="searchable-select" @click.away="subClientDropdownOpen = false">
                        <input type="text" x-model="subClientSearch" @focus="subClientDropdownOpen = true"
                            @click="subClientDropdownOpen = true" @input="filterSubClients()"
                            :disabled="!!projectFromPHP?.sub_client_id || !quote.client_id || loadingSubClients"
                            :placeholder="loadingSubClients ? 'Cargando...' : (!quote.client_id ? 'Primero cliente...' : 'Buscar...')"
                            class="text-sm searchable-select-input" />
                        <div class="searchable-select-icon" :class="{ 'clickable': quote.sub_client_id }">
                            <template x-if="quote.sub_client_id && !projectFromPHP?.sub_client_id">
                                <button @click="clearSubClient()" type="button">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <template x-if="!quote.sub_client_id && !loadingSubClients">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                            <template x-if="loadingSubClients">
                                <span class="spinner"></span>
                            </template>
                        </div>
                        <div x-show="subClientDropdownOpen && filteredSubClients.length > 0" x-transition
                            class="searchable-select-dropdown">
                            <template x-for="subClient in filteredSubClients" :key="subClient.id">
                                <div @click="selectSubClientFromDropdown(subClient)"
                                    :class="{ 'selected': quote.sub_client_id == subClient.id }"
                                    class="searchable-select-item">
                                    <div class="text-sm searchable-select-item-title" x-text="subClient.name"></div>
                                    <div class="searchable-select-item-subtitle"
                                        x-text="subClient.ceco || 'Sin CECO'"></div>
                                </div>
                            </template>
                        </div>
                        <div x-show="subClientDropdownOpen && subClientSearch.length > 0 && filteredSubClients.length === 0 && subClients.length > 0"
                            class="text-sm searchable-select-dropdown searchable-select-empty">
                            Sin resultados
                        </div>
                    </div>
                    <input type="hidden" name="sub_client_id" x-model="quote.sub_client_id">
                </div>

                {{-- Gerente Energy SCI --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Gerente Energy SCI</label>
                    <input x-model="quote.energy_sci_manager" class="text-sm sidebar-input" type="text"
                        placeholder="Nombre del gerente..." />
                    <input type="hidden" name="energy_sci_manager" x-model="quote.energy_sci_manager">
                </div>

                {{-- CECO (auto desde SubCliente) --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">CECO</label>
                    <input x-model="quote.ceco" class="font-mono text-sm sidebar-input" type="text" readonly
                        placeholder="Auto" />
                    <input type="hidden" name="ceco" x-model="quote.ceco">
                </div>
            </div>

            {{-- Segunda fila: Fechas y Estado --}}
            <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-3">
                {{-- Fecha de Cotización --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Fecha Cotización</label>
                    <input x-model="quote.quote_date" class="text-sm sidebar-input" type="date" />
                    <input type="hidden" name="quote_date" x-model="quote.quote_date">
                </div>

                {{-- Fecha de Ejecución --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Fecha Ejecución</label>
                    <input x-model="quote.execution_date" class="text-sm sidebar-input" type="date" />
                    <input type="hidden" name="execution_date" x-model="quote.execution_date">
                </div>

                {{-- Estado --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-500">Estado</label>
                    <select x-model="quote.status" class="text-sm sidebar-input">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                    <input type="hidden" name="status" x-model="quote.status">
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
