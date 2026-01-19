<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <style>
        .form-input-clean {
            border-color: transparent;
            background-color: transparent;
            border-radius: 0.375rem;
            transition: all 0.15s ease;
            font-size: 0.875rem;
            width: 100%;
            padding: 0.375rem 0.5rem;
        }

        .form-input-clean:hover {
            background-color: rgb(249 250 251);
        }

        .dark .form-input-clean:hover {
            background-color: rgb(51 65 85);
        }

        .form-input-clean:focus {
            border-color: rgb(16 185 129);
            box-shadow: 0 0 0 2px rgb(16 185 129 / 0.2);
            outline: none;
        }

        .sidebar-input {
            width: 100%;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border: 1px solid rgb(229 231 235);
            background-color: rgb(249 250 251);
            padding: 0.5rem 0.75rem;
        }

        .dark .sidebar-input {
            border-color: rgb(55 65 81);
            background-color: rgb(30 41 59);
            color: white;
        }

        .sidebar-input:focus {
            border-color: rgb(16 185 129);
            box-shadow: 0 0 0 2px rgb(16 185 129 / 0.2);
            outline: none;
        }

        .card-section {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            border: 1px solid rgb(229 231 235);
            overflow: hidden;
        }

        .dark .card-section {
            background-color: rgb(30 41 59);
            border-color: rgb(55 65 81);
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Modal styles */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 10vh;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            width: 100%;
            max-width: 700px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            animation: modalSlideIn 0.2s ease-out;
        }

        .dark .modal-content {
            background: rgb(30 41 59);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .search-result-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgb(243 244 246);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .search-result-item:hover {
            background-color: rgb(236 253 245);
        }

        .dark .search-result-item {
            border-color: rgb(55 65 81);
        }

        .dark .search-result-item:hover {
            background-color: rgb(6 78 59 / 0.3);
        }
    </style>

    <div x-data="quoteManager()" class="space-y-6">
        {{-- Grid principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Sidebar --}}
            <aside class="lg:col-span-3 space-y-4">
                <div class="card-section p-5">
                    <div
                        class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                        <div class="font-bold text-gray-800 dark:text-white text-lg">SAT INDUSTRIALES</div>
                        <div class="text-xs text-gray-500 font-mono">RUC: 20539249640</div>
                        <div class="h-px bg-emerald-200/50 dark:bg-emerald-800/50 my-3"></div>
                        <div class="flex items-center gap-2">
                            <div
                                class="h-8 w-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">
                                JV</div>
                            <div>
                                <div class="text-xs text-gray-400">Cotizado por</div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Joel Viera</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-section p-5 space-y-4">
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500 text-base">description</span>
                        Datos del Proyecto
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Servicio</label>
                        <input x-model="quote.service_name" class="sidebar-input" placeholder="Nombre del servicio"
                            type="text" />
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

            {{-- Main content --}}
            <main class="lg:col-span-9 space-y-5">
                <template x-for="section in sections" :key="section.key">
                    <div class="card-section">
                        {{-- Header --}}
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                    :class="section.bgClass">
                                    <span class="material-symbols-outlined text-xl" :class="section.iconClass"
                                        x-text="section.icon"></span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-white" x-text="section.title"></h3>
                                    <p class="text-xs text-gray-400" x-text="section.subtitle"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400"
                                    x-text="items[section.key].length + ' items'"></span>
                                <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200"
                                        x-text="'S/ ' + getSectionSubtotal(section.key).toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Items list --}}
                        <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <template x-for="(item, index) in items[section.key]" :key="index">
                                <div
                                    class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 group transition-colors">
                                    <span class="text-xs text-gray-400 w-6 text-center" x-text="index + 1"></span>
                                    <div class="flex-1 grid grid-cols-12 gap-3 items-center">
                                        <div class="col-span-2">
                                            <span
                                                class="font-mono text-xs px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-semibold"
                                                x-text="item.code"></span>
                                        </div>
                                        <div class="col-span-4">
                                            <input x-model="item.description"
                                                class="form-input-clean text-sm text-gray-700 dark:text-gray-200"
                                                type="text" />
                                        </div>
                                        <div class="col-span-1 text-center">
                                            <span class="text-xs text-gray-400 uppercase" x-text="item.unit"></span>
                                        </div>
                                        <div class="col-span-2">
                                            <input x-model.number="item.quantity" @input="recalculate()"
                                                class="form-input-clean text-center font-semibold text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20 rounded-lg w-full"
                                                type="number" min="0.01" step="0.01" />
                                        </div>
                                        <div class="col-span-2">
                                            <input x-model.number="item.unit_price" @input="recalculate()"
                                                class="form-input-clean text-right font-mono text-sm" type="number"
                                                min="0" step="0.01" />
                                        </div>
                                        <div class="col-span-1 text-right">
                                            <span class="font-mono font-bold text-gray-900 dark:text-white text-sm"
                                                x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                                        </div>
                                    </div>
                                    <button @click="removeItem(section.key, index)"
                                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-all p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </template>

                            {{-- Empty state --}}
                            <div x-show="items[section.key].length === 0" class="py-8 text-center text-gray-400">
                                <span
                                    class="material-symbols-outlined text-4xl mb-2 block opacity-50">inventory_2</span>
                                <p class="text-sm">No hay items agregados</p>
                            </div>
                        </div>

                        {{-- Add button --}}
                        <div
                            class="px-5 py-3 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                            <button @click="openSearchModal(section.key)"
                                class="w-full flex items-center justify-center gap-2 py-2.5 text-emerald-600 hover:text-emerald-700 font-medium text-sm rounded-lg border-2 border-dashed border-emerald-200 dark:border-emerald-800 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 transition-all">
                                <span class="material-symbols-outlined text-xl">add_circle</span>
                                <span x-text="'Agregar ' + section.title"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </main>
        </div>

        {{-- Spacer for footer --}}
        <div class="h-24"></div>

        {{-- Search Modal --}}
        <div x-show="searchModal.open" x-cloak @keydown.escape.window="closeSearchModal()" class="modal-backdrop"
            @click.self="closeSearchModal()">
            <div class="modal-content" @click.stop>
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                                <span
                                    class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">search</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">Buscar en Preciario</h3>
                                <p class="text-xs text-gray-400" x-text="'Agregando a: ' + getCurrentSectionTitle()">
                                </p>
                            </div>
                        </div>
                        <button @click="closeSearchModal()"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    {{-- Filter tabs --}}
                    <div class="flex gap-2 mb-4">
                        <button @click="searchModal.filter = null; searchPricelist()"
                            :class="searchModal.filter === null ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                            Todos
                        </button>
                        <template x-for="pt in priceTypes" :key="pt.id">
                            <button @click="searchModal.filter = pt.id; searchPricelist()"
                                :class="searchModal.filter === pt.id ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                x-text="pt.shortName">
                            </button>
                        </template>
                    </div>
                    {{-- Search input --}}
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400">search</span>
                        <input x-ref="searchInput" x-model="searchModal.query" @input.debounce.300ms="searchPricelist()"
                            class="w-full pl-12 pr-4 py-3 bg-gray-100 dark:bg-gray-800 border-0 rounded-xl text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-700 transition-all"
                            type="text" placeholder="Buscar por código o descripción..." autofocus />
                        <div x-show="searchModal.loading" class="absolute right-4 top-3.5">
                            <span
                                class="material-symbols-outlined animate-spin text-emerald-500">progress_activity</span>
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

                    {{-- Empty state --}}
                    <div x-show="searchModal.query.length >= 2 && searchModal.results.length === 0 && !searchModal.loading"
                        class="py-12 text-center text-gray-400">
                        <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">search_off</span>
                        <p class="font-medium">No se encontraron resultados</p>
                        <p class="text-sm">Intenta con otros términos de búsqueda</p>
                    </div>

                    {{-- Initial state --}}
                    <div x-show="searchModal.query.length < 2 && !searchModal.loading"
                        class="py-12 text-center text-gray-400">
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

        {{-- Sticky Footer --}}
        <div
            class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 shadow-lg shadow-gray-900/10 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-sm text-gray-500" x-text="getTotalItems() + ' items'"></span>
                </div>
                <div class="flex items-center gap-8">
                    <div class="text-right">
                        <div class="text-xs text-gray-400 font-medium">Subtotal</div>
                        <div class="font-mono font-bold text-gray-700 dark:text-gray-200"
                            x-text="'S/ ' + getSubtotal().toFixed(2)"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400 font-medium">IGV (18%)</div>
                        <div class="font-mono font-bold text-gray-700 dark:text-gray-200"
                            x-text="'S/ ' + getIGV().toFixed(2)"></div>
                    </div>
                    <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400 font-medium">Total</div>
                        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"
                            x-text="'S/ ' + getTotal().toFixed(2)"></div>
                    </div>
                    <button @click="saveQuote()" :disabled="saving"
                        class="flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/25 transition-all hover:-translate-y-0.5 active:scale-95">
                        <span x-show="!saving" class="material-symbols-outlined">save</span>
                        <span x-show="saving" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quoteManager() {
            return {
                quote: {
                    id: null,
                    service_name: '',
                    client_name: 'Piura - PVH',
                    execution_date: '',
                    ceco: '29150404',
                },

                sections: [
                    { key: 'viaticos', title: 'Viáticos', subtitle: 'Gastos de traslado', icon: 'flight_takeoff', priceTypeId: 3, bgClass: 'bg-blue-100 dark:bg-blue-900/30', iconClass: 'text-blue-600 dark:text-blue-400' },
                    { key: 'suministros', title: 'Suministros', subtitle: 'Materiales y equipos', icon: 'inventory_2', priceTypeId: 2, bgClass: 'bg-amber-100 dark:bg-amber-900/30', iconClass: 'text-amber-600 dark:text-amber-400' },
                    { key: 'mano_obra', title: 'Mano de Obra', subtitle: 'Personal técnico', icon: 'engineering', priceTypeId: 2, bgClass: 'bg-purple-100 dark:bg-purple-900/30', iconClass: 'text-purple-600 dark:text-purple-400' },
                ],

                items: {
                    viaticos: [],
                    suministros: [],
                    mano_obra: [],
                },

                searchModal: {
                    open: false,
                    section: null,
                    query: '',
                    results: [],
                    loading: false,
                    filter: null, // PriceType filter (null = all)
                },

                // PriceType definitions for filters
                priceTypes: [
                    { id: 1, name: 'Mantenimiento Preventivo BT', shortName: 'Preventivo' },
                    { id: 2, name: 'Mantenimiento Correctivos BT', shortName: 'Correctivo' },
                    { id: 3, name: 'Viáticos correctivos BT', shortName: 'Viáticos' },
                ],

                saving: false,
                igvRate: 0.18,

                // Open search modal
                openSearchModal(sectionKey) {
                    this.searchModal.open = true;
                    this.searchModal.section = sectionKey;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    // Set default filter based on section
                    const section = this.sections.find(s => s.key === sectionKey);
                    this.searchModal.filter = section?.priceTypeId || null;
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                },

                // Close search modal
                closeSearchModal() {
                    this.searchModal.open = false;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    this.searchModal.filter = null;
                },

                // Get current section title
                getCurrentSectionTitle() {
                    const section = this.sections.find(s => s.key === this.searchModal.section);
                    return section ? section.title : '';
                },

                // Search pricelist
                async searchPricelist() {
                    if (this.searchModal.query.length < 2) {
                        this.searchModal.results = [];
                        return;
                    }

                    this.searchModal.loading = true;
                    const filterParam = this.searchModal.filter ? `&price_type_id=${this.searchModal.filter}` : '';

                    try {
                        const response = await fetch(`/api/pricelists/search?q=${encodeURIComponent(this.searchModal.query)}${filterParam}&limit=30`);
                        const data = await response.json();
                        this.searchModal.results = data;
                    } catch (error) {
                        console.error('Error searching pricelist:', error);
                        this.searchModal.results = [];
                    } finally {
                        this.searchModal.loading = false;
                    }
                },

                // Select item from modal
                selectItem(result) {
                    const item = {
                        code: result.code,
                        description: result.description,
                        unit: result.unit,
                        quantity: 1,
                        unit_price: result.unit_price,
                    };
                    this.items[this.searchModal.section].push(item);
                    this.closeSearchModal();
                },

                // Remove item
                removeItem(sectionKey, index) {
                    this.items[sectionKey].splice(index, 1);
                },

                // Recalculate
                recalculate() { },

                // Get section subtotal
                getSectionSubtotal(sectionKey) {
                    return this.items[sectionKey].reduce((sum, item) => {
                        return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                    }, 0);
                },

                // Get total items
                getTotalItems() {
                    return Object.values(this.items).reduce((sum, arr) => sum + arr.length, 0);
                },

                // Get subtotal
                getSubtotal() {
                    return this.sections.reduce((sum, section) => sum + this.getSectionSubtotal(section.key), 0);
                },

                // Get IGV
                getIGV() {
                    return this.getSubtotal() * this.igvRate;
                },

                // Get total
                getTotal() {
                    return this.getSubtotal() + this.getIGV();
                },

                // Save quote
                async saveQuote() {
                    this.saving = true;
                    const details = [];
                    let line = 1;

                    this.sections.forEach(section => {
                        this.items[section.key].forEach(item => {
                            if (item.description || item.code) {
                                details.push({
                                    line: line++,
                                    budget_code: item.code,
                                    item_type: section.key.toUpperCase().replace('_', ' '),
                                    description: item.description,
                                    unit: item.unit,
                                    quantity: item.quantity,
                                    unit_price: item.unit_price,
                                });
                            }
                        });
                    });

                    console.log('Saving:', { quote: this.quote, details });
                    await new Promise(r => setTimeout(r, 1000));
                    this.saving = false;
                    alert('¡Cotización guardada! (Demo)');
                },
            };
        }
    </script>
</x-filament-panels::page>