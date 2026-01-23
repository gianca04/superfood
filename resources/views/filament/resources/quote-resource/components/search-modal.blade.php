{{-- Search Drawer Component (Panel Lateral con Multi-selección y Tabs por PriceType) --}}
{{-- Usage: @include('filament.resources.quote-resource.components.search-modal') --}}

{{-- Overlay container (No animation here to avoid delays in children) --}}
<div x-show="searchModal.open" x-cloak @keydown.escape.window="closeSearchModal()"
    class="fixed inset-0 z-50 overflow-hidden">

    {{-- Backdrop (Starts immediately with x-show) --}}
    <div x-show="searchModal.open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 backdrop-blur-none" x-transition:enter-end="opacity-100 backdrop-blur-sm"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 backdrop-blur-sm"
        x-transition:leave-end="opacity-0 backdrop-blur-none" class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
        @click="closeSearchModal()"></div>

    {{-- Drawer Panel (viene de la derecha) --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full" x-show="searchModal.open"
        x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

        <div class="w-screen max-w-lg">
            <div class="flex flex-col h-full bg-white dark:bg-gray-900 shadow-xl">

                {{-- Drawer Header --}}
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">

                            <div>
                                <h3 class="font-black text-sm text-gray-800 dark:text-white uppercase tracking-tight">
                                    Preciario</h3>
                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider"
                                    x-text="'Agregando a: ' + getCurrentSectionTitle()">
                                </p>
                            </div>
                        </div>
                        <button @click="closeSearchModal()"
                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all active:scale-95">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative mt-5">
                        <span
                            class="material-symbols-outlined absolute left-4 top-3 text-gray-400 text-lg">search</span>
                        <input x-ref="searchInput" x-model="searchModal.query" @input.debounce.300ms="searchPricelist()"
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border-gray-100 dark:border-gray-800 rounded-2xl text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all shadow-inner"
                            type="text" placeholder="Buscar por código o descripción..." autofocus />
                        <div x-show="searchModal.loading" class="absolute right-4 top-3">
                            <span
                                class="material-symbols-outlined animate-spin text-emerald-500 text-lg">progress_activity</span>
                        </div>
                    </div>
                </div>

                {{-- Price Type Tabs (solo visibles cuando NO hay búsqueda activa) --}}
                <div x-show="searchModal.query.length < 2"
                    class="flex gap-1 px-4 py-2 overflow-x-auto border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <template x-for="(group, index) in searchModal.priceTypeGroups" :key="group.price_type.id">
                        <button @click="selectPriceTypeTab(index)"
                            :class="searchModal.activeTabIndex === index ? 'bg-emerald-500 text-white shadow-sm' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-all">
                            <span x-text="group.price_type.name"></span>
                            <span class="ml-1 opacity-75" x-text="'(' + group.items.length + ')'"></span>
                        </button>
                    </template>
                </div>

                {{-- Selected Items Badge --}}
                <div x-show="searchModal.selectedItems.length > 0"
                    class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">
                            <span x-text="searchModal.selectedItems.length"></span> item(s) seleccionado(s)
                        </span>
                        <button @click="searchModal.selectedItems = []"
                            class="text-xs text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
                            Limpiar
                        </button>
                    </div>
                </div>

                {{-- Results List --}}
                <div class="flex-1 overflow-y-auto" x-ref="resultsContainer" @scroll="handleScroll($event)">

                    {{-- Modo Búsqueda: Mostrar resultados de búsqueda --}}
                    <template x-if="searchModal.query.length >= 2">
                        <div>
                            <template x-for="result in searchModal.results" :key="result.id">
                                <div @click="toggleItemSelection(result)"
                                    :class="isItemSelected(result.id) ? 'bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500' : 'border-l-4 border-transparent hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 cursor-pointer transition-colors">
                                    <div class="flex items-start gap-3">
                                        {{-- Checkbox --}}
                                        <div class="flex-shrink-0 pt-0.5">
                                            <div :class="isItemSelected(result.id) ? 'bg-emerald-500 border-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600'"
                                                class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200">
                                                <span x-show="isItemSelected(result.id)"
                                                    class="material-symbols-outlined text-white text-base font-bold">check</span>
                                            </div>
                                        </div>
                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span
                                                    class="font-mono text-xs px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 font-semibold"
                                                    x-text="result.code"></span>
                                                <span class="text-xs text-gray-400 uppercase"
                                                    x-text="result.unit"></span>
                                            </div>
                                            <p class="text-sm text-gray-700 dark:text-gray-200 line-clamp-2"
                                                x-text="result.description"></p>
                                        </div>
                                        {{-- Price --}}
                                        <div class="flex-shrink-0 text-right">
                                            <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                                                x-text="'S/ ' + result.unit_price.toFixed(2)"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty State para búsqueda --}}
                            <div x-show="searchModal.results.length === 0 && !searchModal.loading"
                                class="py-12 text-center text-gray-400">
                                <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">search_off</span>
                                <p class="text-sm font-medium">No se encontraron resultados</p>
                            </div>
                        </div>
                    </template>

                    {{-- Modo Tabs: Mostrar items por PriceType --}}
                    <template x-if="searchModal.query.length < 2">
                        <div>
                            {{-- Título del Tab Activo --}}
                            <div x-show="searchModal.priceTypeGroups.length > 0"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                                    x-text="searchModal.priceTypeGroups[searchModal.activeTabIndex]?.price_type?.name || 'Cargando...'">
                                </p>
                            </div>

                            {{-- Items del Tab Activo --}}
                            <template x-for="item in getCurrentTabItems()" :key="item.id">
                                <div @click="toggleItemSelection(item)"
                                    :class="isItemSelected(item.id) ? 'bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500' : 'border-l-4 border-transparent hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 cursor-pointer transition-colors">
                                    <div class="flex items-start gap-3">
                                        {{-- Checkbox --}}
                                        <div class="flex-shrink-0 pt-0.5">
                                            <div :class="isItemSelected(item.id) ? 'bg-emerald-500 border-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600'"
                                                class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200">
                                                <span x-show="isItemSelected(item.id)"
                                                    class="material-symbols-outlined text-white text-base font-bold">check</span>
                                            </div>
                                        </div>
                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span
                                                    class="font-mono text-xs px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 font-semibold"
                                                    x-text="item.code"></span>
                                                <span class="text-xs text-gray-400 uppercase" x-text="item.unit"></span>
                                            </div>
                                            <p class="text-sm text-gray-700 dark:text-gray-200 line-clamp-2"
                                                x-text="item.description"></p>
                                        </div>
                                        {{-- Price --}}
                                        <div class="flex-shrink-0 text-right">
                                            <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                                                x-text="'S/ ' + item.unit_price.toFixed(2)"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Loading más items --}}
                            <div x-show="searchModal.loadingMore" class="py-4 text-center">
                                <span
                                    class="material-symbols-outlined animate-spin text-emerald-500 text-2xl">progress_activity</span>
                                <p class="text-xs text-gray-400 mt-1">Cargando más items...</p>
                            </div>

                            {{-- Fin de la lista --}}
                            <div x-show="!searchModal.loadingMore && !getCurrentTabHasMore() && getCurrentTabItems().length > 0"
                                class="py-3 text-center border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-400">
                                    <span
                                        class="material-symbols-outlined text-sm align-middle mr-1">check_circle</span>
                                    Fin de la lista
                                </p>
                            </div>

                            {{-- Empty State para tabs --}}
                            <div x-show="searchModal.priceTypeGroups.length === 0 && !searchModal.loadingInitial"
                                class="py-12 text-center text-gray-400">
                                <span
                                    class="material-symbols-outlined text-4xl mb-2 block opacity-50">inventory_2</span>
                                <p class="text-sm font-medium">No hay items disponibles</p>
                            </div>

                            {{-- Loading inicial --}}
                            <div x-show="searchModal.loadingInitial" class="py-12 text-center">
                                <span
                                    class="material-symbols-outlined animate-spin text-emerald-500 text-4xl">progress_activity</span>
                                <p class="text-sm text-gray-400 mt-2">Cargando preciario...</p>
                            </div>
                        </div>
                    </template>

                    {{-- Initial State (antes de escribir) --}}
                    <div x-show="searchModal.query.length >= 1 && searchModal.query.length < 2 && !searchModal.loading"
                        class="py-12 text-center text-gray-400">
                        <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">keyboard</span>
                        <p class="text-sm font-medium">Sigue escribiendo...</p>
                        <p class="text-xs">Ingresa al menos 2 caracteres para buscar</p>
                    </div>
                </div>

                {{-- Drawer Footer --}}
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-[0_-10px_20px_-5px_rgba(0,0,0,0.05)]">
                    <div class="flex items-center justify-between gap-4">
                        <span
                            class="text-[10px] uppercase font-bold text-gray-400 tracking-widest px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <template x-if="searchModal.query.length >= 2">
                                <span x-text="searchModal.results.length + ' resultados'"></span>
                            </template>
                            <template x-if="searchModal.query.length < 2">
                                <span x-text="getCurrentTabItems().length + ' items'"></span>
                            </template>
                        </span>

                        <div class="flex gap-3">
                            <button @click="closeSearchModal()"
                                class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 rounded-xl transition-all">
                                Cancelar
                            </button>
                            <button @click="addSelectedItems()" :disabled="searchModal.selectedItems.length === 0"
                                :class="searchModal.selectedItems.length === 0 ? 'opacity-50 grayscale cursor-not-allowed' : 'hover:bg-emerald-700 hover:scale-105 shadow-emerald-500/20 shadow-lg active:scale-95'"
                                class="px-6 py-2.5 text-xs font-black text-white bg-emerald-600 rounded-xl transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                AGREGAR (<span x-text="searchModal.selectedItems.length"></span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>