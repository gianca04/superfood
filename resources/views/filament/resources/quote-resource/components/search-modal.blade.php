{{-- Search Modal Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.search-modal') --}}

<div x-show="searchModal.open" x-cloak @keydown.escape.window="closeSearchModal()" class="modal-backdrop"
    @click.self="closeSearchModal()">
    <div class="modal-content" @click.stop>
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">search</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">Buscar en Preciario</h3>
                        <p class="text-xs text-gray-400" x-text="'Agregando a: ' + getCurrentSectionTitle()"></p>
                    </div>
                </div>
                <button @click="closeSearchModal()"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-2 mb-4">
                <button @click="searchModal.filter = null; searchPricelist()"
                    :class="searchModal.filter === null ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                    Todos
                </button>
                <template x-for="pt in priceTypes" :key="pt.id">
                    <button @click="searchModal.filter = pt.id; searchPricelist()"
                        :class="searchModal.filter === pt.id ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" x-text="pt.shortName">
                    </button>
                </template>
            </div>

            {{-- Search Input --}}
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400">search</span>
                <input x-ref="searchInput" x-model="searchModal.query" @input.debounce.300ms="searchPricelist()"
                    class="w-full pl-12 pr-4 py-3 bg-gray-100 dark:bg-gray-800 border-0 rounded-xl text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-700 transition-all"
                    type="text" placeholder="Buscar por código o descripción..." autofocus />
                <div x-show="searchModal.loading" class="absolute right-4 top-3.5">
                    <span class="material-symbols-outlined animate-spin text-emerald-500">progress_activity</span>
                </div>
            </div>
        </div>

        {{-- Modal Results --}}
        <div class="max-h-[50vh] overflow-y-auto">
            <template x-for="result in searchModal.results" :key="result.id">
                <div @click="selectItem(result)" class="search-result-item">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="font-mono text-xs px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 font-semibold"
                                    x-text="result.code"></span>
                                <span class="text-xs text-gray-400 uppercase" x-text="result.unit"></span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-200" x-text="result.description"></p>
                        </div>
                        <div class="text-right ml-4">
                            <div class="font-bold text-emerald-600 dark:text-emerald-400"
                                x-text="'S/ ' + result.unit_price.toFixed(2)"></div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="searchModal.query.length >= 2 && searchModal.results.length === 0 && !searchModal.loading"
                class="py-12 text-center text-gray-400">
                <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">search_off</span>
                <p class="font-medium">No se encontraron resultados</p>
                <p class="text-sm">Intenta con otros términos de búsqueda</p>
            </div>

            {{-- Initial State --}}
            <div x-show="searchModal.query.length < 2 && !searchModal.loading" class="py-12 text-center text-gray-400">
                <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">inventory_2</span>
                <p class="font-medium">Escribe para buscar</p>
                <p class="text-sm">Ingresa al menos 2 caracteres</p>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>Presiona <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded font-mono">ESC</kbd>
                    para cerrar</span>
                <span x-text="searchModal.results.length + ' resultados'"></span>
            </div>
        </div>
    </div>
</div>