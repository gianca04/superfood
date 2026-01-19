{{-- Quote Sidebar Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-sidebar') --}}

<aside class="space-y-4 lg:col-span-3">
    {{-- Provider Info --}}
    <div class="p-5 card-section">
        <div
            class="p-4 border bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border-emerald-100 dark:border-emerald-800/30">
            <div class="text-lg font-bold text-gray-800 dark:text-white">SAT INDUSTRIALES</div>
            <div class="font-mono text-xs text-gray-500">RUC: 20539249640</div>
            <div class="h-px my-3 bg-emerald-200/50 dark:bg-emerald-800/50"></div>
            <div class="flex items-center gap-2">
                <div
                    class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full bg-emerald-500">
                    {{ substr(auth()->user()->employee->full_name, 0, 1) . (strpos(auth()->user()->employee->full_name, ' ') !== false ? substr(auth()->user()->employee->full_name, strpos(auth()->user()->employee->full_name, ' ') + 1, 1) : '') }}
                </div>
                <div>
                    <div class="text-xs text-gray-400">Cotizado por</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ auth()->user()->employee->full_name }}</div>
                    <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Project Details --}}
    <div class="p-5 space-y-4 card-section">
        <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-gray-500 uppercase">
            <span class="text-base material-symbols-outlined text-emerald-500">description</span>
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
            <input x-model="quote.client_name" @input="searchClients" class="sidebar-input"
                placeholder="Buscar cliente..." type="text" />
            <input type="hidden" name="client_id" x-model="quote.client_id">
            <div x-show="clientSearchResults.length > 0"
                class="mt-1 overflow-y-auto bg-white border rounded-md shadow-sm max-h-32 dark:bg-gray-800">
                <div x-for="client in clientSearchResults" :key="client.id" @click="selectClient(client)"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="font-medium">@{{ client.business_name }}</div>
                    <div class="text-xs text-gray-500">@{{ client.document_number }}</div>
                </div>
            </div>
        </div>
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500">SubCliente / Tienda</label>
            <input x-model="quote.sub_client_name" @input="searchSubClients" :disabled="!quote.client_id"
                class="sidebar-input" placeholder="Seleccionar cliente primero..." type="text" />
            <input type="hidden" name="sub_client_id" x-model="quote.sub_client_id">
            <div x-show="subClientSearchResults.length > 0"
                class="mt-1 overflow-y-auto bg-white border rounded-md shadow-sm max-h-32 dark:bg-gray-800">
                <div x-for="subClient in subClientSearchResults" :key="subClient.id"
                    @click="selectSubClient(subClient)"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="font-medium">@{{ subClient.name }}</div>
                    <div class="text-xs text-gray-500">@{{ subClient.ceco }}</div>
                </div>
            </div>
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
