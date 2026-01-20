{{-- Quote Sidebar Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-sidebar') --}}

<aside class="space-y-4 lg:col-span-3">

    {{-- Project Details --}}
    <div class="p-5 space-y-4 card-section">

        <div class="flex items-center gap-3">
            {{-- Avatar con iniciales --}}
            <div
                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full bg-emerald-500 shrink-0">
                {{ substr(auth()->user()->employee->full_name, 0, 1) . (strpos(auth()->user()->employee->full_name, ' ') !== false ? substr(auth()->user()->employee->full_name, strpos(auth()->user()->employee->full_name, ' ') + 1, 1) : '') }}
            </div>
            {{-- Nombre --}}
            <div>
                <div class="text-xs text-gray-400">Cotizado por</div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ auth()->user()->employee->full_name }}
                </div>
            </div>
            <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">
        </div>

        <div class="h-px my-3 bg-emerald-200/50 dark:bg-emerald-800/50"></div>

        <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-gray-500 uppercase">
            <span class="text-base material-symbols-outlined">description</span>
            Datos del Proyecto
        </div>
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500">Categoría</label>
            <select x-model="quote.quote_category_id" class="sidebar-input">
                <option value="">Seleccionar categoría...</option>
                <template x-for="category in quoteCategories" :key="category.id">
                    <option :value="category.id" x-text="category.name"></option>
                </template>
            </select>
            <input type="hidden" name="quote_category_id" x-model="quote.quote_category_id">
        </div>
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500">Cliente</label>
            {{-- Searchable Select para clientes --}}
            <div class="searchable-select">
                {{-- Input de búsqueda --}}
                <input type="text" x-model="clientSearch" @focus="clientDropdownOpen = true"
                    @click="clientDropdownOpen = true" @input="filterClients()" placeholder="Buscar cliente..."
                    class="searchable-select-input" />

                {{-- Icono de búsqueda/limpiar --}}
                <div class="searchable-select-icon" :class="{'clickable': quote.client_id}">
                    <template x-if="quote.client_id">
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

                {{-- Dropdown de resultados --}}
                <div x-show="clientDropdownOpen && filteredClients.length > 0" x-transition
                    @click.away="clientDropdownOpen = false" class="searchable-select-dropdown">
                    <template x-for="client in filteredClients" :key="client.id">
                        <div @click="selectClientFromDropdown(client)"
                            :class="{'selected': quote.client_id == client.id}" class="searchable-select-item">
                            <div class="searchable-select-item-title" x-text="client.business_name"></div>
                            <div class="searchable-select-item-subtitle" x-text="client.document_number || 'Sin RUC'">
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Mensaje cuando no hay resultados --}}
                <div x-show="clientDropdownOpen && clientSearch.length > 0 && filteredClients.length === 0"
                    class="searchable-select-dropdown searchable-select-empty">
                    No se encontraron resultados para "<span x-text="clientSearch"></span>"
                </div>
            </div>
            <input type="hidden" name="client_id" x-model="quote.client_id">
        </div>
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500">SubCliente / Tienda</label>
            {{-- Searchable Select para subclientes --}}
            <div class="searchable-select">
                {{-- Input de búsqueda --}}
                <input type="text" x-model="subClientSearch" @focus="subClientDropdownOpen = true"
                    @click="subClientDropdownOpen = true" @input="filterSubClients()"
                    :disabled="!quote.client_id || loadingSubClients"
                    :placeholder="loadingSubClients ? 'Cargando...' : (!quote.client_id ? 'Seleccionar cliente primero...' : 'Buscar subcliente...')"
                    class="searchable-select-input" />

                {{-- Icono de búsqueda/limpiar/loading --}}
                <div class="searchable-select-icon" :class="{'clickable': quote.sub_client_id}">
                    <template x-if="quote.sub_client_id">
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

                {{-- Dropdown de resultados --}}
                <div x-show="subClientDropdownOpen && filteredSubClients.length > 0" x-transition
                    @click.away="subClientDropdownOpen = false" class="searchable-select-dropdown">
                    <template x-for="subClient in filteredSubClients" :key="subClient.id">
                        <div @click="selectSubClientFromDropdown(subClient)"
                            :class="{'selected': quote.sub_client_id == subClient.id}" class="searchable-select-item">
                            <div class="searchable-select-item-title" x-text="subClient.name"></div>
                            <div class="searchable-select-item-subtitle" x-text="subClient.ceco || 'Sin CECO'"></div>
                        </div>
                    </template>
                </div>

                {{-- Mensaje cuando no hay resultados --}}
                <div x-show="subClientDropdownOpen && subClientSearch.length > 0 && filteredSubClients.length === 0 && subClients.length > 0"
                    class="searchable-select-dropdown searchable-select-empty">
                    No se encontraron resultados para "<span x-text="subClientSearch"></span>"
                </div>
            </div>
            <input type="hidden" name="sub_client_id" x-model="quote.sub_client_id">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500">Fecha Ejec.</label>
                <input x-model="quote.execution_date" class="sidebar-input" type="date" />
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500">CECO</label>
                <input x-model="quote.ceco" class="font-mono sidebar-input" type="text" />
            </div>
        </div>
    </div>
</aside>