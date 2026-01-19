<x-filament-panels::page>
    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/quote-form.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Main Container with Alpine --}}
    {{-- Pasamos los datos desde PHP directamente, eliminando llamadas API innecesarias --}}
    <div x-data="quoteManager(@js($quoteCategories), @js($clients), @js($priceTypes))" class="space-y-6">
        {{-- Grid Layout --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Sidebar --}}
            @include('filament.resources.quote-resource.components.quote-sidebar')

            {{-- Main Content --}}
            <main class="space-y-5 lg:col-span-9">
                <template x-for="section in sections" :key="section.key">
                    @include('filament.resources.quote-resource.components.section-card')
                </template>
            </main>
        </div>

        {{-- Spacer for sticky footer --}}
        <div class="h-24"></div>

        {{-- Search Modal --}}
        @include('filament.resources.quote-resource.components.search-modal')

        {{-- Sticky Footer --}}
        @include('filament.resources.quote-resource.components.quote-footer')
    </div>

    {{-- Alpine Component (inline fallback if not using modules) --}}
    <script>
        function quoteManager(categoriesFromPHP = [], clientsFromPHP = [], priceTypesFromPHP = []) {
            return {
                // Quote header
                quote: {
                    id: null,
                    service_name: '',
                    client_id: null,
                    sub_client_id: null,
                    quote_category_id: null,
                    execution_date: '',
                    ceco: '',
                },

                // Sections
                sections: [{
                    key: 'viaticos',
                    title: 'Viáticos',
                    subtitle: 'Gastos de traslado',
                    icon: 'flight_takeoff',
                    priceTypeId: 3,
                    bgClass: 'bg-blue-100 dark:bg-blue-900/30',
                    iconClass: 'text-blue-600 dark:text-blue-400'
                },
                {
                    key: 'suministros',
                    title: 'Suministros',
                    subtitle: 'Materiales y equipos',
                    icon: 'inventory_2',
                    priceTypeId: 2,
                    bgClass: 'bg-amber-100 dark:bg-amber-900/30',
                    iconClass: 'text-amber-600 dark:text-amber-400'
                },
                {
                    key: 'mano_obra',
                    title: 'Mano de Obra',
                    subtitle: 'Personal técnico',
                    icon: 'engineering',
                    priceTypeId: 2,
                    bgClass: 'bg-purple-100 dark:bg-purple-900/30',
                    iconClass: 'text-purple-600 dark:text-purple-400'
                },
                ],

                // Items per section
                items: {
                    viaticos: [],
                    suministros: [],
                    mano_obra: []
                },

                // Modal state
                searchModal: {
                    open: false,
                    section: null,
                    query: '',
                    results: [],
                    loading: false,
                    filter: null,
                },

                // SubClientes cargados desde API (cuando se selecciona cliente)
                subClients: [],
                loadingSubClients: false,

                // Searchable select para subclientes
                subClientSearch: '',
                subClientDropdownOpen: false,
                filteredSubClients: [],

                // Datos cargados directamente desde PHP (sin API)
                quoteCategories: categoriesFromPHP,
                allClients: clientsFromPHP, // Clientes para el select (menos de 10)

                // Price types - cargados desde PHP
                priceTypes: priceTypesFromPHP.map(pt => ({
                    id: pt.id,
                    name: pt.name,
                    shortName: pt.name.split(' ')[0] // Primer palabra como shortName
                })),

                saving: false,
                igvRate: 0.18,

                // Modal
                openSearchModal(sectionKey) {
                    this.searchModal.open = true;
                    this.searchModal.section = sectionKey;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    const section = this.sections.find(s => s.key === sectionKey);
                    this.searchModal.filter = section?.priceTypeId || null;
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                },

                closeSearchModal() {
                    this.searchModal.open = false;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    this.searchModal.filter = null;
                },

                getCurrentSectionTitle() {
                    const section = this.sections.find(s => s.key === this.searchModal.section);
                    return section ? section.title : '';
                },

                // Las categorías ya están cargadas desde PHP - no se necesita llamada API
                // quoteCategories ya está inicializado con categoriesFromPHP

                // Search
                async searchPricelist() {
                    if (this.searchModal.query.length < 2) {
                        this.searchModal.results = [];
                        return;
                    }
                    this.searchModal.loading = true;
                    try {
                        const response = await fetch(
                            `/api/pricelists/search?q=${encodeURIComponent(this.searchModal.query)}&limit=30`
                        );
                        this.searchModal.results = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchModal.results = [];
                    } finally {
                        this.searchModal.loading = false;
                    }
                },

                // Cuando se selecciona un cliente del select - carga subclientes
                async onClientChange(event) {
                    const clientId = event.target.value;
                    console.log('🎯 Cliente seleccionado ID:', clientId);

                    // Reset subclient and search when client changes
                    this.quote.sub_client_id = null;
                    this.quote.ceco = '';
                    this.subClients = [];
                    this.filteredSubClients = [];
                    this.subClientSearch = '';
                    this.subClientDropdownOpen = false;

                    // Si no hay cliente seleccionado, no cargar subclientes
                    if (!clientId) {
                        console.log('🔄 No hay cliente, reset subclientes');
                        return;
                    }

                    // Cargar subclientes desde API
                    await this.loadSubClients(clientId);
                },

                // Cargar todos los subclientes de un cliente desde API
                async loadSubClients(clientId) {
                    this.loadingSubClients = true;
                    console.log('📦 Cargando subclientes para client_id:', clientId);

                    try {
                        const response = await fetch(`/api/sub-clients?client_id=${clientId}`);
                        const data = await response.json();
                        // La API devuelve paginación, tomamos los datos
                        this.subClients = data.data || data;
                        // Inicializar filteredSubClients con todos
                        this.filteredSubClients = [...this.subClients];
                        console.log('✅ Subclientes cargados:', this.subClients.length);
                    } catch (error) {
                        console.error('❌ Error cargando subclientes:', error);
                        this.subClients = [];
                        this.filteredSubClients = [];
                    } finally {
                        this.loadingSubClients = false;
                    }
                },

                // Filtrar subclientes localmente mientras se escribe
                filterSubClients() {
                    const query = this.subClientSearch.toLowerCase().trim();
                    if (!query) {
                        this.filteredSubClients = [...this.subClients];
                    } else {
                        this.filteredSubClients = this.subClients.filter(sc =>
                            sc.name.toLowerCase().includes(query) ||
                            (sc.ceco && sc.ceco.toLowerCase().includes(query))
                        );
                    }
                    this.subClientDropdownOpen = true;
                },

                // Seleccionar subcliente desde el dropdown
                selectSubClientFromDropdown(subClient) {
                    console.log('🎯 SubCliente seleccionado:', subClient);
                    this.quote.sub_client_id = subClient.id;
                    this.subClientSearch = subClient.name;
                    this.quote.ceco = subClient.ceco || 'No definido';
                    this.subClientDropdownOpen = false;
                    console.log('📝 CECO actualizado a:', this.quote.ceco);
                },

                // Limpiar selección de subcliente
                clearSubClient() {
                    this.quote.sub_client_id = null;
                    this.subClientSearch = '';
                    this.quote.ceco = '';
                    this.filteredSubClients = [...this.subClients];
                    console.log('🗑️ SubCliente limpiado');
                },

                // Items
                selectItem(result) {
                    this.items[this.searchModal.section].push({
                        code: result.code,
                        description: result.description,
                        unit: result.unit,
                        quantity: 1,
                        unit_price: result.unit_price,
                    });
                    this.closeSearchModal();
                },

                removeItem(sectionKey, index) {
                    this.items[sectionKey].splice(index, 1);
                },

                recalculate() { },

                // Calculations
                getSectionSubtotal(sectionKey) {
                    return this.items[sectionKey].reduce((sum, item) =>
                        sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0);
                },

                getTotalItems() {
                    return Object.values(this.items).reduce((sum, arr) => sum + arr.length, 0);
                },

                getSubtotal() {
                    return this.sections.reduce((sum, s) => sum + this.getSectionSubtotal(s.key), 0);
                },

                getIGV() {
                    return this.getSubtotal() * this.igvRate;
                },
                getTotal() {
                    return this.getSubtotal() + this.getIGV();
                },

                // Save
                async saveQuote() {
                    this.saving = true;

                    try {
                        console.log('🚀 Iniciando guardado de cotización...');

                        // Preparar datos de la cotización
                        const quoteData = {
                            service_name: this.quote.service_name,
                            execution_date: this.quote.execution_date,
                            ceco: this.quote.ceco,
                            client_id: this.quote.client_id,
                            sub_client_id: this.quote.sub_client_id,
                            quote_category_id: this.quote.quote_category_id,
                        };

                        console.log('📋 Datos de cotización preparados:', quoteData);

                        // Enviar petición al API
                        const response = await fetch('/api/quotes', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                    'content') || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(quoteData)
                        });

                        const result = await response.json();

                        if (response.ok) {
                            console.log('✅ Cotización guardada exitosamente:', result);

                            // Resetear formulario o redirigir
                            alert('¡Cotización guardada exitosamente!');

                            // Opcional: resetear formulario
                            // this.resetForm();

                        } else {
                            console.error('❌ Error al guardar cotización:', result);
                            alert('Error al guardar: ' + (result.message || 'Error desconocido'));
                        }

                    } catch (error) {
                        console.error('💥 Error de red al guardar cotización:', error);
                        alert('Error de conexión al guardar la cotización');
                    } finally {
                        this.saving = false;
                    }
                },

                // Reset form after successful save
                resetForm() {
                    console.log('🔄 Reseteando formulario...');
                    this.quote = {
                        id: null,
                        service_name: '',
                        client_id: null,
                        sub_client_id: null,
                        quote_category_id: null,
                        execution_date: '',
                        ceco: '',
                    };
                    this.subClients = [];
                    this.filteredSubClients = [];
                    this.subClientSearch = '';
                    this.subClientDropdownOpen = false;
                    // Reset items if needed
                    Object.keys(this.items).forEach(key => {
                        this.items[key] = [];
                    });
                },
            };
        }
    </script>
</x-filament-panels::page>