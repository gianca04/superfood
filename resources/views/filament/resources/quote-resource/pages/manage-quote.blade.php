<x-filament-panels::page>
    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/quote-form.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Main Container with Alpine --}}
    <div x-data="quoteManager()" x-init="loadCategories()" class="space-y-6">
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
        function quoteManager() {
            return {
                // Quote header
                quote: {
                    id: null,
                    service_name: '',
                    client_id: null,
                    client_name: '',
                    sub_client_id: null,
                    sub_client_name: '',
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

                // Client/SubClient search
                clientSearchResults: [],
                subClientSearchResults: [],
                quoteCategories: [],

                // Price types
                priceTypes: [{
                        id: 1,
                        name: 'Mantenimiento Preventivo BT',
                        shortName: 'Preventivo'
                    },
                    {
                        id: 2,
                        name: 'Mantenimiento Correctivos BT',
                        shortName: 'Correctivo'
                    },
                    {
                        id: 3,
                        name: 'Viáticos correctivos BT',
                        shortName: 'Viáticos'
                    },
                ],

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

                // Load quote categories
                async loadCategories() {
                    console.log('📂 Loading quote categories...');
                    try {
                        const response = await fetch('/api/quote-categories');
                        this.quoteCategories = await response.json();
                        console.log('✅ Loaded categories:', this.quoteCategories);
                    } catch (error) {
                        console.error('❌ Error loading categories:', error);
                        this.quoteCategories = [];
                    }
                },

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

                // Client search
                async searchClients() {
                    if (this.quote.client_name.length < 2) {
                        this.clientSearchResults = [];
                        return;
                    }
                    console.log('🔍 Searching clients for:', this.quote.client_name);
                    try {
                        const response = await fetch(
                            `/api/clients/search?q=${encodeURIComponent(this.quote.client_name)}&limit=10`
                        );
                        this.clientSearchResults = await response.json();
                        console.log('✅ Client search results:', this.clientSearchResults);
                    } catch (error) {
                        console.error('❌ Client search error:', error);
                        this.clientSearchResults = [];
                    }
                },

                // SubClient search
                async searchSubClients() {
                    if (!this.quote.client_id || this.quote.sub_client_name.length < 2) {
                        this.subClientSearchResults = [];
                        return;
                    }
                    console.log('🔍 Searching subclients for client_id:', this.quote.client_id, 'query:', this.quote
                        .sub_client_name);
                    try {
                        const response = await fetch(
                            `/api/sub-clients/search?client_id=${this.quote.client_id}&q=${encodeURIComponent(this.quote.sub_client_name)}&limit=10`
                        );
                        this.subClientSearchResults = await response.json();
                        console.log('✅ SubClient search results:', this.subClientSearchResults);
                    } catch (error) {
                        console.error('❌ SubClient search error:', error);
                        this.subClientSearchResults = [];
                    }
                },

                // Select client
                selectClient(client) {
                    console.log('🎯 Selected client:', client);
                    this.quote.client_id = client.id;
                    this.quote.client_name = client.business_name;
                    this.clientSearchResults = [];
                    // Reset subclient when client changes
                    this.quote.sub_client_id = null;
                    this.quote.sub_client_name = '';
                    this.quote.ceco = ''; // Reset CECO when client changes
                    this.subClientSearchResults = [];
                    console.log('🔄 Reset subclient and CECO for new client');
                },

                // Select subclient
                selectSubClient(subClient) {
                    console.log('🎯 Selected subclient:', subClient);
                    this.quote.sub_client_id = subClient.id;
                    this.quote.sub_client_name = subClient.name;
                    // Set CECO from subclient or show "No definido" if empty
                    this.quote.ceco = subClient.ceco || 'No definido';
                    console.log('📝 Updated CECO to:', this.quote.ceco);
                    this.subClientSearchResults = [];
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

                recalculate() {},

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
                            client_name: this.quote.client_name,
                            sub_client_name: this.quote.sub_client_name,
                            execution_date: this.quote.execution_date,
                            ceco: this.quote.ceco,
                            employee_id: this.quote.employee_id,
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
                        client_name: '',
                        sub_client_id: null,
                        sub_client_name: '',
                        quote_category_id: null,
                        execution_date: '',
                        ceco: '',
                    };
                    this.clientSearchResults = [];
                    this.subClientSearchResults = [];
                    // Reset items if needed
                    Object.keys(this.items).forEach(key => {
                        this.items[key] = [];
                    });
                },
            };
        }
    </script>
</x-filament-panels::page>
