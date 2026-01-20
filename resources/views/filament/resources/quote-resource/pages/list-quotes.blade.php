<x-filament-panels::page>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    @vite(['resources/css/quote-cards.css', 'resources/js/app.js', 'resources/css/app.css'])

    <div x-data="quoteIndex()" x-init="fetchQuotes(), initPagination()" class="quote-cards-container">
        {{-- Header con estadísticas --}}
        <div class="mb-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="flex items-center gap-4 p-4 bg-white border border-gray-200 rounded-lg quote-stat-card dark:bg-gray-800 dark:border-gray-700">
                    <span
                        class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900/40">
                        <span
                            class="text-2xl text-blue-600 material-symbols-outlined dark:text-blue-300">assignment</span>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Cotizaciones</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_quotes">0</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 p-4 bg-white border border-gray-200 rounded-lg quote-stat-card dark:bg-gray-800 dark:border-gray-700">
                    <span
                        class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900/40">
                        <span
                            class="text-2xl text-blue-600 material-symbols-outlined dark:text-blue-300">attach_money</span>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monto Total</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            x-text="'S/ ' + formatNumber(stats.total_amount)">S/ 0.00</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 p-4 border border-green-200 rounded-lg quote-stat-card bg-green-50 dark:bg-green-900/30 dark:border-green-800">
                    <span
                        class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full dark:bg-green-900/40">
                        <span
                            class="text-2xl text-green-600 material-symbols-outlined dark:text-green-300">check_circle</span>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-green-700 dark:text-green-400">Aprobadas</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-300" x-text="stats.approved">0</p>
                    </div>
                </div>
                <div
                    class="flex items-center col-span-1 gap-4 p-4 border border-blue-200 rounded-lg quote-stat-card bg-blue-50 dark:bg-blue-900/30 dark:border-blue-800 sm:col-span-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900/40">
                        <span
                            class="text-2xl text-blue-600 material-symbols-outlined dark:text-blue-300">pending_actions</span>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-400">Por Hacer</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-300" x-text="stats.pending">0</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Buscador y Filtros --}}
        <div class="flex flex-col gap-4 mb-6 quote-search-filter sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1 min-w-[300px]">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-400 material-symbols-outlined">search</span>
                </span>
                <input type="text" x-model="search" @input.debounce.500ms="fetchQuotes()"
                    class="block w-full py-2 pl-10 pr-4 border border-gray-300 rounded-lg quote-search-input dark:bg-gray-800 dark:border-gray-700"
                    placeholder="Buscar por número, cliente, cotizador o servicio...">
            </div>

            <select x-model="filterStatus" @change="fetchQuotes()"
                class="px-4 py-2 border border-gray-300 rounded-lg quote-select-filter dark:bg-gray-800 dark:border-gray-700">
                <option value="">Todos los estados</option>
                <option value="POR HACER">Por Hacer</option>
                <option value="ENVIADO">Enviado</option>
                <option value="APROBADO">Aprobado</option>
                <option value="RECHAZADA">Rechazada</option>
            </select>

            {{-- Select para filtrar por cotizador --}}
            <select x-model="filterEmployeeId" @change="fetchQuotes()"
                class="px-4 py-2 border border-gray-300 rounded-lg quote-select-filter dark:bg-gray-800 dark:border-gray-700">
                <option value="">Todos los cotizadores</option>
                <template x-for="emp in stats.employees" :key="emp.id">
                    <option :value="emp.id" x-text="emp.fullname"></option>
                </template>
            </select>

            {{-- Filtro por rango de precios --}}
            <div class="flex items-center gap-2">
                <input type="number" x-model.number="filterMinTotal" @input.debounce.500ms="fetchQuotes()"
                    class="w-24 px-2 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700"
                    placeholder="Mín S/" min="0">
                <span class="text-gray-500">-</span>
                <input type="number" x-model.number="filterMaxTotal" @input.debounce.500ms="fetchQuotes()"
                    class="w-24 px-2 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700"
                    placeholder="Máx S/" min="0">
            </div>
        </div>

        {{-- Grid de Cards --}}
        <div class="grid grid-cols-1 gap-6 quote-card-grid sm:grid-cols-3">
            <template x-for="quote in paginatedQuotes" :key="quote.id">
                <div
                    class="overflow-hidden bg-white border border-gray-200 quote-card rounded-xl dark:bg-gray-800 dark:border-gray-700">

                    {{-- Header Card --}}
                    <div class="p-4 border-b border-gray-100 quote-card-header dark:border-gray-700">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                    Nº Solicitud
                                </p>
                                <p class="text-lg font-bold text-blue-700 dark:text-blue-400"
                                    x-text="quote.request_number || 'S/N'"></p>
                            </div>
                            <span :class="getStatusClass(quote.status)" class="px-3 py-1 text-xs font-bold rounded-full"
                                x-text="quote.status"></span>
                        </div>
                    </div>

                    {{-- Monto Principal --}}
                    <div class="px-4 py-3 border-b border-gray-100 quote-card-amount dark:border-gray-700">
                        <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Monto Total</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            x-text="'S/ ' + formatNumber(quote.total_amount || 0)"></p>
                    </div>

                    {{-- Contenido --}}
                    <div class="p-4 space-y-3">
                        {{-- Cliente --}}
                        <div class="flex items-center gap-3">
                            <span class="flex-shrink-0 p-2 rounded-lg quote-icon-blue">
                                <span
                                    class="text-base text-blue-600 dark:text-blue-400 material-symbols-outlined">apartment</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                <p class="font-medium text-gray-900 truncate dark:text-white"
                                    x-text="quote.sub_client?.name || 'Sin cliente'"></p>
                            </div>

                        </div>

                        {{-- Fecha --}}
                        <div class="flex items-center gap-3">
                            <span class="flex-shrink-0 p-2 rounded-lg quote-icon-amber">
                                <span
                                    class="text-base text-amber-600 dark:text-amber-400 material-symbols-outlined">calendar_month</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Fecha Emisión</p>
                                <p class="font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(quote.quote_date)"></p>
                            </div>
                        </div>

                        {{-- Empleado --}}
                        <div class="flex items-center gap-3">
                            <span class="flex-shrink-0 p-2 rounded-lg quote-icon-purple">
                                <span
                                    class="text-base text-purple-600 dark:text-purple-400 material-symbols-outlined">person</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cotizador</p>
                                <p class="font-medium text-gray-900 truncate dark:text-white"
                                    x-text="quote.employee
                                        ? ((quote.employee.first_name ? quote.employee.first_name : '') +
                                           (quote.employee.last_name ? ' ' + quote.employee.last_name : '') || 'Sin nombre')
                                        : 'No asignado'">
                                </p>
                            </div>
                        </div>

                        {{-- Categoría --}}
                        <div class="flex items-center gap-3" x-show="quote.quote_category">
                            <span class="flex-shrink-0 p-2 rounded-lg quote-icon-green">
                                <span
                                    class="text-base text-green-600 dark:text-green-400 material-symbols-outlined">category</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Categoría</p>
                                <p class="font-medium text-gray-900 truncate dark:text-white"
                                    x-text="quote.quote_category?.name || '-'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer con Acciones --}}
                    <div
                        class="flex flex-wrap gap-2 p-3 border-t border-gray-100 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700">
                        <a :href="'/dashboard/quotes/' + quote.id + '/edit'"
                            class="flex-1 px-3 py-2 text-xs font-semibold text-center text-gray-700 bg-white border border-gray-300 rounded-lg quote-btn-edit dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                            <span class="inline text-sm material-symbols-outlined">edit</span>
                            Editar
                        </a>
                        <a :href="'/quotes/' + quote.id + '/pdf'"
                            class="flex-1 px-3 py-2 text-xs font-semibold text-center text-white bg-blue-600 rounded-lg quote-btn-pdf hover:bg-blue-500"
                            target="_blank">
                            <span class="inline text-sm material-symbols-outlined">picture_as_pdf</span>
                            Descargar PDF
                        </a>
                        <a :href="'/quotes/' + quote.id + '/excel'"
                            class="flex-1 px-3 py-2 text-xs font-semibold text-center text-white bg-green-700 rounded-lg hover:bg-green-600"
                            target="_blank">
                            <span class="inline text-sm material-symbols-outlined">grid_on</span>
                            Descargar Excel
                        </a>
                        <a :href="'/quotes/' + quote.id + '/preview'" target="_blank"
                            class="flex-1 px-3 py-2 text-xs font-semibold text-center text-white bg-green-600 rounded-lg hover:bg-green-500">
                            <span class="inline text-sm material-symbols-outlined">visibility</span>
                            Previsualizar
                        </a>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div x-show="!loading && quotes.length === 0" class="quote-empty-state">
            <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">No se encontraron cotizaciones</p>
            <p class="text-sm text-gray-500 dark:text-gray-500">Intenta con otros términos de búsqueda</p>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="flex justify-center py-12">
            <x-filament::loading-indicator class="w-10 h-10" />
        </div>

        {{-- Paginación --}}
        <div x-show="!loading && quotes.length > 0" class="quote-pagination">
            <button @click="previousPage()" :disabled="currentPage === 1" class="px-4 py-2 border rounded-lg">
                Anterior
            </button>
            <span class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                x-text="'Página ' + currentPage + ' de ' + totalPages"></span>
            <button @click="nextPage()" :disabled="currentPage === totalPages" class="px-4 py-2 border rounded-lg">
                Siguiente
            </button>
        </div>
    </div>

    <script>
        function quoteIndex() {
            return {
                quotes: [],
                loading: false,
                search: '',
                filterStatus: '',
                filterEmployeeId: '',
                filterMinTotal: null,
                filterMaxTotal: null,
                filterCategoryId: '',
                currentPage: 1,
                totalPages: 1,
                stats: {
                    total_quotes: 0,
                    total_amount: 0,
                    approved: 0,
                    pending: 0,
                    employees: [],
                    categories: [],
                },
                perPage: 12,
                get paginatedQuotes() {
                    // Solo muestra 12 por página
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.quotes.slice(start, start + this.perPage);
                },
                async fetchQuotes() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            q: this.search,
                            status: this.filterStatus,
                            page: this.currentPage
                        });
                        if (this.filterEmployeeId) {
                            params.append('employee_id', this.filterEmployeeId);
                        }
                        if (this.filterMinTotal !== null && this.filterMinTotal !== '') {
                            params.append('min_total', this.filterMinTotal);
                        }
                        if (this.filterMaxTotal !== null && this.filterMaxTotal !== '') {
                            params.append('max_total', this.filterMaxTotal);
                        }
                        const response = await fetch(`/quotes?${params}`);
                        const data = await response.json();

                        this.quotes = data.data || [];
                        this.totalPages = data.last_page || 1;

                        await this.fetchStatistics();
                    } catch (e) {
                        console.error('Error fetching quotes:', e);
                        this.quotes = [];
                    }
                    this.loading = false;
                },

                async printQuote(quoteId) {
                    // Al usar 'D' en el controlador mPDF, el navegador iniciará la descarga automáticamente
                    window.open(`/quotes/${quoteId}/pdf`, '_blank');
                },

                initPagination() {
                    this.fetchQuotes();
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.fetchQuotes();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.fetchQuotes();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                getStatusClass(status) {
                    const classes = {
                        'APROBADO': 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'POR HACER': 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'ENVIADO': 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'RECHAZADA': 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                    };
                    return classes[status] ||
                        'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300';
                },

                formatDate(date) {
                    if (!date) return '-';
                    return new Date(date).toLocaleDateString('es-PE', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                formatNumber(num) {
                    return parseFloat(num || 0).toLocaleString('es-PE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },


                async fetchStatistics() {
                    try {
                        const response = await fetch('/quotes/stats');
                        const data = await response.json();
                        this.stats = data;
                    } catch (e) {
                        console.error('Error fetching statistics:', e);
                    }
                },
            }
        }
    </script>
</x-filament-panels::page>
