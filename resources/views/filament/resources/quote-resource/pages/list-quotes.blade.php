<x-filament-panels::page>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    @vite(['resources/css/quote-cards.css', 'resources/js/app.js', 'resources/css/app.css'])

    <div x-data="quoteIndex()" x-init="fetchQuotes(), initPagination()" class="quote-cards-container">
        {{-- Header con estadísticas --}}
        <div class="mb-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
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
                    class="flex items-center gap-4 p-4 border border-blue-200 rounded-lg quote-stat-card bg-blue-50 dark:bg-blue-900/30 dark:border-blue-800">
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
                <option value="Pendiente">Por Hacer</option>
                <option value="Enviado">Enviado</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Anulado">Anulado</option>
            </select>

            {{-- Select para filtrar por cotizador --}}
            <select x-model="filterEmployeeId" @change="fetchQuotes()"
                class="px-4 py-2 border border-gray-300 rounded-lg quote-select-filter dark:bg-gray-800 dark:border-gray-700">
                <option value="">Todos los cotizadores</option>
                <template x-for="emp in stats.employees" :key="emp.id">
                    <option :value="emp.id" x-text="emp.fullname"></option>
                </template>
            </select>

            <select x-model="filterCategoryId" @change="fetchQuotes()"
                class="px-4 py-2 border border-gray-300 rounded-lg quote-select-filter dark:bg-gray-800 dark:border-gray-700">
                <option value="">Todas las categorías</option>
                <template x-for="cat in stats.categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
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
                <div class="quote-card">
                    {{-- 1. Logo del Cliente (Expandido) --}}
                    <div class="quote-card-logo-container">
                        <template x-if="quote.sub_client?.client?.logo">
                            <img :src="'/storage/' + quote.sub_client.client.logo" class="quote-card-logo"
                                alt="Logo Cliente">
                        </template>
                        <template x-if="!quote.sub_client?.client?.logo">
                            <div class="flex flex-col items-center opacity-40">
                                <span class="text-4xl material-symbols-outlined">corporate_fare</span>
                                <span class="text-[10px] font-bold">SAT INDUSTRIALES</span>
                            </div>
                        </template>
                    </div>

                    {{-- 2. Identificación y Estado --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <p class="text-[9px] uppercase font-bold text-gray-400 tracking-tighter">Nº Solicitud</p>
                            <p class="text-sm font-black text-blue-700" x-text="quote.request_number || 'S/N'"></p>
                        </div>
                        <span :class="getStatusClass(quote.status)"
                            class="px-3 py-1 text-[10px] font-black rounded-full shadow-sm uppercase tracking-wider"
                            x-text="quote.status">
                        </span>
                    </div>

                    {{-- 3. Monto Total (Diseño Impactante) --}}
                    <div class="quote-card-amount-banner">
                        <p class="text-[10px] uppercase font-bold text-blue-100/80" x-text="quote.service_name"></p>
                        <p class="text-2xl font-black text-white"
                            x-text="'S/ ' + formatNumber(quote.total_amount || 0)"></p>
                    </div>

                    {{-- 4. Información Detallada --}}
                    <div class="p-4 space-y-3">
                        {{-- Sede / Cliente --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 text-blue-600 rounded-lg bg-blue-50">
                                <span class="text-sm material-symbols-outlined">location_on</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Unidad / Sede</p>
                                <p class="text-xs font-bold text-gray-800 truncate"
                                    x-text="quote.sub_client?.name || 'Sin sede'"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-50">
                            {{-- Fecha --}}
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-amber-500 material-symbols-outlined">calendar_month</span>
                                <span class="text-[10px] font-bold text-gray-600"
                                    x-text="formatDate(quote.quote_date)"></span>
                            </div>
                            {{-- Categoría --}}
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-green-600 material-symbols-outlined">label</span>
                                <span class="text-[10px] font-bold text-gray-600 truncate"
                                    x-text="quote.quote_category?.name || '-'"></span>
                            </div>
                        </div>

                        {{-- Cotizador --}}
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-purple-500 material-symbols-outlined">person</span>
                            <span class="text-[10px] font-medium text-gray-500 italic" x-text="quote.employee
                                        ? ((quote.employee.first_name ? quote.employee.first_name : '') +
                                           (quote.employee.last_name ? ' ' + quote.employee.last_name : '') || 'Sin nombre')
                                        : 'No asignado'"></span>
                        </div>
                    </div>

                    {{-- 5. Botonera de Acciones (Compact Grid) --}}
                    <div class="flex items-center justify-between gap-2 p-3 border-t border-gray-100 bg-gray-50">
                        <div class="flex gap-2">
                            <a :href="'/dashboard/quotes/' + quote.id + '/edit'"
                                class="flex items-center justify-center w-8 h-8 text-gray-600 transition-all bg-white border border-gray-200 rounded-lg hover:text-blue-600 hover:border-blue-300 hover:shadow-sm"
                                title="Editar">
                                <span class="text-sm material-symbols-outlined">edit</span>
                            </a>
                            <a :href="'/quotes/' + quote.id + '/preview'" target="_blank"
                                class="flex items-center justify-center w-8 h-8 text-gray-600 transition-all bg-white border border-gray-200 rounded-lg hover:text-gray-900 hover:border-gray-400 hover:shadow-sm"
                                title="Ver">
                                <span class="text-sm material-symbols-outlined">visibility</span>
                            </a>
                        </div>

                        <div class="flex gap-2">
                            <a :href="'/quotes/' + quote.id + '/pdf'" target="_blank"
                                class="flex items-center justify-center w-8 h-8 text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-md"
                                title="Descargar PDF">
                                <span class="text-sm material-symbols-outlined">picture_as_pdf</span>
                            </a>
                            <a :href="'/quotes/' + quote.id + '/excel'" target="_blank"
                                class="flex items-center justify-center w-8 h-8 text-white transition-all rounded-lg bg-emerald-600 hover:bg-emerald-700 hover:shadow-md"
                                title="Descargar Excel">
                                <span class="text-sm material-symbols-outlined">grid_on</span>
                            </a>
                            <button @click="deleteQuote(quote.id)"
                                class="flex items-center justify-center w-8 h-8 text-white transition-all bg-red-500 rounded-lg hover:bg-red-600 hover:shadow-md"
                                title="Eliminar">
                                <span class="text-sm material-symbols-outlined">delete</span>
                            </button>
                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                async fetchCategories() {
                    try {
                        const response = await fetch('/quotes/categories');
                        const data = await response.json();
                        this.stats.categories = data;
                    } catch (e) {
                        console.error('Error fetching categories:', e);
                    }
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
                        if (this.filterCategoryId) {
                            params.append('category', this.filterCategoryId);
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
                        // await this.fetchCategories();
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

                async deleteQuote(quoteId) {
                    const result = await Swal.fire({
                        title: '¿Estás seguro?',
                        text: "No podrás revertir esto",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow-lg transform transition-all hover:scale-105 active:scale-95 mx-2',
                            cancelButton: 'bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow-lg transform transition-all hover:scale-105 active:scale-95 mx-2',
                            popup: 'rounded-xl shadow-2xl dark:bg-gray-800 dark:text-white',
                            title: 'text-xl font-bold text-gray-800 dark:text-white',
                            htmlContainer: 'text-gray-600 dark:text-gray-300'
                        }
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(`/quotes/${quoteId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.getAttribute('content') || '',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });

                            if (response.ok) {
                                Swal.fire(
                                    '¡Eliminado!',
                                    'La cotización ha sido eliminada.',
                                    'success'
                                );
                                this.fetchQuotes(); // Recargar la lista
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar la cotización.',
                                    'error'
                                );
                            }
                        } catch (error) {
                            console.error('Error deleting quote:', error);
                            Swal.fire(
                                'Error',
                                'Error de conexión.',
                                'error'
                            );
                        }
                    }
                },

                initPagination() {
                    this.fetchCategories();
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
                        'Aprobado': 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'Pendiente': 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'Enviado': 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'Anulado': 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
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
                        this.stats = {
                            ...this.stats,
                            ...data
                        };
                    } catch (e) {
                        console.error('Error fetching statistics:', e);
                    }
                },
            }
        }
    </script>
</x-filament-panels::page>