<x-filament-panels::page>
    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/quote-form.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Main Container with Alpine --}}
    {{-- Pasamos los datos desde PHP directamente, eliminando llamadas API innecesarias --}}
    <div x-data="quoteManager(@js($quoteCategories), @js($clients), @js($priceTypes), @js($record ?? null))" class="space-y-4">

        {{-- Collapsible Sidebar (Top Panel) --}}
        @include('filament.resources.quote-resource.components.quote-sidebar')

        {{-- Main Content (Full Width) --}}
        <main class="space-y-5">
            <template x-for="section in sections" :key="section.key">
                @include('filament.resources.quote-resource.components.section-card')
            </template>
        </main>

        {{-- Spacer for sticky footer --}}
        <div class="h-24"></div>

        {{-- Search Modal --}}
        @include('filament.resources.quote-resource.components.search-modal')

        {{-- Sticky Footer --}}
        @include('filament.resources.quote-resource.components.quote-footer')
    </div>

    {{-- Alpine Component (inline fallback if not using modules) --}}
    <script>
        function quoteManager(categoriesFromPHP = [], clientsFromPHP = [], priceTypesFromPHP = [], existingQuote = null) {
            return {
                // Sidebar state (collapsible)
                sidebarOpen: true,

                // Quote header - todos los campos del modelo
                quote: {
                    id: null,
                    request_number: '',
                    employee_id: null,
                    service_name: '',
                    client_id: null,
                    sub_client_id: null,
                    quote_category_id: null,
                    energy_sci_manager: '',
                    ceco: '',
                    status: 'POR HACER',
                    quote_date: new Date().toISOString().split('T')[0], // Fecha actual por defecto
                    execution_date: '',
                },

                // Sections
                sections: [{
                        key: 'viaticos',
                        title: 'Viáticos',
                        icon: 'flight_takeoff',
                        priceTypeId: 3,
                        bgClass: 'bg-blue-100 dark:bg-blue-900/30',
                        iconClass: 'text-blue-600 dark:text-blue-400'
                    },
                    {
                        key: 'suministros',
                        title: 'Suministros',
                        icon: 'inventory_2',
                        priceTypeId: 2,
                        bgClass: 'bg-amber-100 dark:bg-amber-900/30',
                        iconClass: 'text-amber-600 dark:text-amber-400'
                    },
                    {
                        key: 'mano_obra',
                        title: 'Mano de Obra',
                        icon: 'engineering',
                        priceTypeId: 2,
                        bgClass: 'bg-purple-100 dark:bg-purple-900/30',
                        iconClass: 'text-purple-600 dark:text-purple-400'
                    },
                ],

                // Column Resizing
                columnWidths: {
                    code: 80,
                    description: 300,
                    comment: 150,
                    unit: 60,
                    quantity: 70,
                    unit_price: 90,
                    subtotal: 100
                },
                resizing: null,
                startX: 0,
                startWidth: 0,

                startResize(column, event) {
                    this.resizing = column;
                    this.startX = event.pageX;
                    this.startWidth = this.columnWidths[column];
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';

                    const moveHandler = (e) => {
                        if (this.resizing !== column) return;
                        const diff = e.pageX - this.startX;
                        // Mínimo 40px
                        this.columnWidths[column] = Math.max(40, this.startWidth + diff);
                    };

                    const upHandler = () => {
                        this.resizing = null;
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                        document.removeEventListener('mousemove', moveHandler);
                        document.removeEventListener('mouseup', upHandler);
                    };

                    document.addEventListener('mousemove', moveHandler);
                    document.addEventListener('mouseup', upHandler);
                },

                // Items per section
                items: {
                    viaticos: [],
                    suministros: [],
                    mano_obra: []
                },

                // Modal state (ahora es un drawer con multi-selección y tabs por PriceType)
                searchModal: {
                    open: false,
                    section: null,
                    query: '',
                    results: [],
                    loading: false,
                    filter: null,
                    selectedItems: [], // Para multi-selección
                    // Nuevas propiedades para tabs con items iniciales
                    priceTypeGroups: [], // Array de {price_type, items, has_more, page}
                    activeTabIndex: 0,
                    loadingInitial: false,
                    loadingMore: false,
                },

                // SubClientes cargados desde API (cuando se selecciona cliente)
                subClients: [],
                loadingSubClients: false,

                // Searchable select para subclientes
                subClientSearch: '',
                subClientDropdownOpen: false,
                filteredSubClients: [],

                // Searchable select para clientes
                clientSearch: '',
                clientDropdownOpen: false,
                filteredClients: [],

                // Datos cargados directamente desde PHP (sin API)
                quoteCategories: categoriesFromPHP,
                allClients: clientsFromPHP,

                // Price types - cargados desde PHP
                priceTypes: priceTypesFromPHP.map(pt => ({
                    id: pt.id,
                    name: pt.name,
                    shortName: pt.name.split(' ')[0]
                })),

                saving: false,
                igvRate: 0.18,

                // Inicializar filteredClients con todos los clientes
                init() {
                    this.filteredClients = [...this.allClients];

                    if (existingQuote) {
                        console.log('✏️ Editando cotización:', existingQuote);

                        // Cargar datos básicos
                        this.quote.id = existingQuote.id;
                        this.quote.request_number = existingQuote.request_number || '';
                        this.quote.employee_id = existingQuote.employee_id || null;
                        this.quote.service_name = existingQuote.service_name || '';
                        this.quote.client_id = existingQuote.sub_client?.client_id || null;
                        this.quote.sub_client_id = existingQuote.sub_client_id;
                        this.quote.quote_category_id = existingQuote.quote_category_id;
                        this.quote.energy_sci_manager = existingQuote.energy_sci_manager || '';
                        this.quote.ceco = existingQuote.ceco || existingQuote.sub_client?.ceco || '';
                        this.quote.status = existingQuote.status;

                        // Fechas (asegurar formato YYYY-MM-DD)
                        if (existingQuote.quote_date) {
                            this.quote.quote_date = existingQuote.quote_date.split('T')[0];
                        }
                        if (existingQuote.execution_date) {
                            this.quote.execution_date = existingQuote.execution_date.split('T')[0];
                        }

                        // Inicializar campos de búsqueda de cliente/subcliente
                        if (this.quote.client_id) {
                            const client = this.allClients.find(c => c.id === this.quote.client_id);
                            if (client) {
                                this.clientSearch = client.business_name;
                                // Cargar subclientes
                                this.loadSubClients(client.id).then(() => {
                                    if (this.quote.sub_client_id) {
                                        const subClient = this.subClients.find(sc => sc.id === this.quote
                                            .sub_client_id);
                                        if (subClient) {
                                            this.subClientSearch = subClient.name;
                                        }
                                    }
                                });
                            }
                        }

                        // Cargar Items
                        if (existingQuote.quote_details && existingQuote.quote_details.length > 0) {
                            existingQuote.quote_details.forEach(detail => {
                                const item = {
                                    code: detail.pricelist?.sat_line || '',
                                    description: detail.pricelist?.sat_description || detail.description || '',
                                    comment: detail.comment || '',
                                    unit: detail.pricelist?.unit?.name || 'UND',
                                    quantity: parseFloat(detail.quantity),
                                    unit_price: parseFloat(detail.unit_price),
                                    pricelist_id: detail.pricelist_id,
                                };

                                switch (detail.item_type) {
                                    case 'VIATICOS':
                                        this.items.viaticos.push(item);
                                        break;
                                    case 'SUMINISTRO': // Singular en BD
                                        this.items.suministros.push(item);
                                        break;
                                    case 'MANO DE OBRA':
                                        this.items.mano_obra.push(item);
                                        break;
                                }
                            });
                        }
                    }
                },

                // Drawer (Panel lateral) con multi-selección y tabs por PriceType
                async openSearchModal(sectionKey) {
                    this.searchModal.open = true;
                    this.searchModal.section = sectionKey;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    this.searchModal.selectedItems = []; // Reset selección
                    this.searchModal.activeTabIndex = 0;

                    const section = this.sections.find(s => s.key === sectionKey);
                    this.searchModal.filter = section?.priceTypeId || null;

                    // Cargar items iniciales si no están cargados
                    if (this.searchModal.priceTypeGroups.length === 0) {
                        await this.loadInitialItems();
                    }

                    // Establecer el tab activo según la sección (si tiene priceTypeId)
                    if (section?.priceTypeId) {
                        const tabIndex = this.searchModal.priceTypeGroups.findIndex(
                            g => g.price_type.id === section.priceTypeId
                        );
                        if (tabIndex >= 0) {
                            this.searchModal.activeTabIndex = tabIndex;
                        }
                    }

                    this.$nextTick(() => this.$refs.searchInput?.focus());
                },

                // Cargar items iniciales (primeros 15 de cada PriceType)
                async loadInitialItems() {
                    this.searchModal.loadingInitial = true;
                    try {
                        const response = await fetch('/api/pricelists/initial-items');
                        const data = await response.json();
                        // Agregar page tracker a cada grupo
                        this.searchModal.priceTypeGroups = data.map(group => ({
                            ...group,
                            page: 1, // Página actual para paginación
                        }));
                        console.log('📦 Items iniciales cargados:', this.searchModal.priceTypeGroups.length, 'tipos');
                    } catch (error) {
                        console.error('Error cargando items iniciales:', error);
                        this.searchModal.priceTypeGroups = [];
                    } finally {
                        this.searchModal.loadingInitial = false;
                    }
                },

                closeSearchModal() {
                    this.searchModal.open = false;
                    this.searchModal.query = '';
                    this.searchModal.results = [];
                    this.searchModal.filter = null;
                    this.searchModal.selectedItems = [];
                    this.searchModal.activeTabIndex = 0;
                },

                getCurrentSectionTitle() {
                    const section = this.sections.find(s => s.key === this.searchModal.section);
                    return section ? section.title : '';
                },

                // Seleccionar tab de PriceType
                selectPriceTypeTab(index) {
                    this.searchModal.activeTabIndex = index;
                    // Scroll al inicio del contenedor
                    this.$refs.resultsContainer?.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },

                // Obtener items del tab activo
                getCurrentTabItems() {
                    const group = this.searchModal.priceTypeGroups[this.searchModal.activeTabIndex];
                    return group?.items || [];
                },

                // Verificar si hay más items para cargar en el tab activo
                getCurrentTabHasMore() {
                    const group = this.searchModal.priceTypeGroups[this.searchModal.activeTabIndex];
                    return group?.has_more || false;
                },

                // Manejar scroll para cargar más items (infinite scroll)
                handleScroll(event) {
                    // Solo en modo tabs (no en búsqueda)
                    if (this.searchModal.query.length >= 2) return;
                    if (this.searchModal.loadingMore) return;
                    if (!this.getCurrentTabHasMore()) return;

                    const container = event.target;
                    const scrollBottom = container.scrollHeight - container.scrollTop - container.clientHeight;

                    // Cargar más cuando esté cerca del final (100px)
                    if (scrollBottom < 100) {
                        this.loadMoreItems();
                    }
                },

                // Cargar más items del tab activo
                async loadMoreItems() {
                    const groupIndex = this.searchModal.activeTabIndex;
                    const group = this.searchModal.priceTypeGroups[groupIndex];

                    if (!group || !group.has_more || this.searchModal.loadingMore) return;

                    this.searchModal.loadingMore = true;

                    try {
                        const nextPage = group.page + 1;
                        const response = await fetch(
                            `/api/pricelists/by-price-type?price_type_id=${group.price_type.id}&page=${nextPage}&per_page=30`
                        );
                        const data = await response.json();

                        // Agregar nuevos items al grupo
                        this.searchModal.priceTypeGroups[groupIndex].items = [
                            ...group.items,
                            ...data.data
                        ];
                        this.searchModal.priceTypeGroups[groupIndex].page = nextPage;
                        this.searchModal.priceTypeGroups[groupIndex].has_more = data.meta.has_more;

                        console.log(`📥 Cargados ${data.data.length} items más para ${group.price_type.name}`);
                    } catch (error) {
                        console.error('Error cargando más items:', error);
                    } finally {
                        this.searchModal.loadingMore = false;
                    }
                },

                // Toggle selección de item (multi-selección)
                toggleItemSelection(result) {
                    const index = this.searchModal.selectedItems.findIndex(i => i.id === result.id);
                    if (index === -1) {
                        this.searchModal.selectedItems.push(result);
                    } else {
                        this.searchModal.selectedItems.splice(index, 1);
                    }
                },

                isItemSelected(resultId) {
                    return this.searchModal.selectedItems.some(i => i.id === resultId);
                },

                // Agregar todos los items seleccionados
                addSelectedItems() {
                    this.searchModal.selectedItems.forEach(result => {
                        this.items[this.searchModal.section].push({
                            code: result.code,
                            description: result.description,
                            comment: '',
                            unit: result.unit,
                            quantity: 1,
                            unit_price: result.unit_price,
                            pricelist_id: result.id, // ID del pricelist para backend
                        });
                    });
                    console.log(`✅ Agregados ${this.searchModal.selectedItems.length} items a ${this.searchModal.section}`);
                    this.searchModal.selectedItems = []; // Limpiar selección después de agregar
                    // NO cerramos el drawer para permitir seguir agregando
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
                        // Construir URL con parámetros de búsqueda y filtro de tipo de precio
                        let url = `/api/pricelists/search?q=${encodeURIComponent(this.searchModal.query)}&limit=30`;

                        // Agregar filtro por price_type_id si está seleccionado
                        if (this.searchModal.filter) {
                            url += `&price_type_id=${this.searchModal.filter}`;
                        }

                        const response = await fetch(url);
                        this.searchModal.results = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchModal.results = [];
                    } finally {
                        this.searchModal.loading = false;
                    }
                },

                // Filtrar clientes localmente mientras se escribe
                filterClients() {
                    const query = this.clientSearch.toLowerCase().trim();
                    if (!query) {
                        this.filteredClients = [...this.allClients];
                    } else {
                        this.filteredClients = this.allClients.filter(c =>
                            c.business_name.toLowerCase().includes(query) ||
                            (c.document_number && c.document_number.includes(query))
                        );
                    }
                    this.clientDropdownOpen = true;
                },

                // Seleccionar cliente desde el dropdown
                selectClientFromDropdown(client) {
                    console.log('🎯 Cliente seleccionado:', client);
                    this.quote.client_id = client.id;
                    this.clientSearch = client.business_name;
                    this.clientDropdownOpen = false;

                    // Reset subclient when client changes
                    this.quote.sub_client_id = null;
                    this.quote.ceco = '';
                    this.subClients = [];
                    this.filteredSubClients = [];
                    this.subClientSearch = '';
                    this.subClientDropdownOpen = false;

                    // Cargar subclientes del nuevo cliente
                    this.loadSubClients(client.id);
                },

                // Limpiar selección de cliente
                clearClient() {
                    this.quote.client_id = null;
                    this.clientSearch = '';
                    this.filteredClients = [...this.allClients];

                    // Reset subclient too
                    this.quote.sub_client_id = null;
                    this.quote.ceco = '';
                    this.subClients = [];
                    this.filteredSubClients = [];
                    this.subClientSearch = '';
                    console.log('🗑️ Cliente y SubCliente limpiados');
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
                        comment: '', // Campo de comentario editable
                        unit: result.unit,
                        quantity: 1,
                        unit_price: result.unit_price,
                        pricelist_id: result.id, // ID del pricelist para backend
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
                            request_number: this.quote.request_number,
                            employee_id: this.quote.employee_id,
                            service_name: this.quote.service_name,
                            sub_client_id: this.quote.sub_client_id,
                            quote_category_id: this.quote.quote_category_id,
                            energy_sci_manager: this.quote.energy_sci_manager,
                            ceco: this.quote.ceco,
                            status: this.quote.status,
                            quote_date: this.quote.quote_date,
                            execution_date: this.quote.execution_date,
                            items: [
                                ...this.items.viaticos.map(item => ({
                                    ...item,
                                    item_type: 'VIATICOS',
                                    budget_code: item.code,
                                    pricelist_id: item.pricelist_id
                                })),
                                ...this.items.suministros.map(item => ({
                                    ...item,
                                    item_type: 'SUMINISTRO',
                                    budget_code: item.code,
                                    pricelist_id: item.pricelist_id
                                })),
                                ...this.items.mano_obra.map(item => ({
                                    ...item,
                                    item_type: 'MANO DE OBRA',
                                    budget_code: item.code,
                                    pricelist_id: item.pricelist_id
                                }))
                            ]
                        };

                        console.log('📋 Datos de cotización preparados:', quoteData);

                        // Determinar URL y Método (POST para crear, PUT para actualizar)
                        const url = this.quote.id ? `/quotes/${this.quote.id}` : '/quotes';
                        const method = this.quote.id ? 'PUT' : 'POST';

                        // Enviar petición al API
                        const response = await fetch(url, {
                            method: method,
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

                            // Si es una actualización, solo notificar. Si es creación, redirigir.
                            if (this.quote.id) {
                                alert('¡Cotización actualizada exitosamente!');
                                // Opcional: recargar datos o mantener estado
                            } else {
                                // Redirect to edit page
                                window.location.href = `/dashboard/quotes/${result.id}/edit`;
                            }

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
                        energy_sci_manager: '',
                        ceco: '',
                        status: 'POR HACER',
                        quote_date: new Date().toISOString().split('T')[0],
                        execution_date: '',
                    };
                    // Reset client searchable select
                    this.clientSearch = '';
                    this.clientDropdownOpen = false;
                    this.filteredClients = [...this.allClients];
                    // Reset subclient searchable select
                    this.subClients = [];
                    this.filteredSubClients = [];
                    this.subClientSearch = '';
                    this.subClientDropdownOpen = false;
                    // Reset items
                    Object.keys(this.items).forEach(key => {
                        this.items[key] = [];
                    });
                },
            };
        }
    </script>
</x-filament-panels::page>
