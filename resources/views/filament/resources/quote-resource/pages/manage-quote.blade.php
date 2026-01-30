<x-filament-panels::page>
    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/quote-form.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Main Container with Alpine --}}
    {{-- Pasamos los datos desde PHP directamente, eliminando llamadas API innecesarias --}}
    <div x-data="quoteManager(
        @js($quoteCategories),
        @js($clients),
        @js($priceTypes),
        @js($record ?? null),
        @js($project ?? null),
        @js($quoteCount ?? 1),
        @js($subClientId ?? null),
        @js($serviceCode ?? null),
        @js($projectId ?? null),
        @js($suggestedRequestNumber ?? null) {{-- <-- NUEVO --}}
    )" class="space-y-4">

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

        {{-- Puedes mostrar el project_id en la vista principal si lo necesitas --}}

    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Alpine Component (inline fallback if not using modules) --}}
    <script>
        function quoteManager(categoriesFromPHP = [], clientsFromPHP = [], priceTypesFromPHP = [], existingQuote = null,
            projectFromPHP = null, quoteCount = 1, subClientId = null, serviceCode = null, projectId = null,
            suggestedRequestNumber = null) {
            return {
                // Sidebar state (collapsible)
                sidebarOpen: true,

                // Quote header - todos los campos del modelo
                quote: {
                    id: null,
                    request_number: suggestedRequestNumber || projectFromPHP?.service_code ||
                        '', // Si quieres igualar al service_code
                    employee_id: null,
                    project_name: projectFromPHP?.name || existingQuote?.project?.name || '',
                    client_id: projectFromPHP?.client_id || null,
                    sub_client_id: projectFromPHP?.sub_client_id || subClientId || null,
                    quote_category_id: categoriesFromPHP.find(c => c.name === 'II.EE. Baja Tensión')?.id || null,
                    energy_sci_manager: 'Raul Quispe',
                    ceco: '',
                    status: 'Pendiente',
                    quote_date: new Date().toISOString().split('T')[0], // Fecha actual por defecto
                    execution_date: '',
                    service_code: projectFromPHP?.service_code || '', // <-- Aquí
                    project_id: projectFromPHP?.id || projectId || null,
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

                // Drag and Drop State
                draggingItem: null,
                draggingSection: null,
                draggingIndex: null,

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

                // Drag and Drop Handlers
                dragStart(sectionKey, index) {
                    this.draggingItem = this.items[sectionKey][index];
                    this.draggingSection = sectionKey;
                    this.draggingIndex = index;
                    console.log('Drag start:', sectionKey, index);
                },

                dragOver(event) {
                    // Necessary to allow dropping
                    // event.preventDefault(); // Handled in view
                    return false;
                },

                dragDrop(sectionKey, targetIndex) {
                    // Only allow dropping within the same section for now
                    if (this.draggingSection !== sectionKey || this.draggingIndex === null) return;

                    const items = this.items[sectionKey];
                    const itemToMove = items[this.draggingIndex];

                    // Remove from old position
                    items.splice(this.draggingIndex, 1);
                    // Insert at new position
                    items.splice(targetIndex, 0, itemToMove);

                    // Reset state
                    this.draggingItem = null;
                    this.draggingSection = null;
                    this.draggingIndex = null;
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
                subClientSearchTimeout: null, // Debounce timeout para búsqueda de subclientes

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

                    console.log('[INIT] projectFromPHP:', projectFromPHP);
                    console.log('[INIT] existingQuote:', existingQuote);

                    if (existingQuote) {
                        console.log('✏️ Editando cotización:', existingQuote);

                        // Cargar datos básicos
                        this.quote.id = existingQuote.id;
                        this.quote.request_number = existingQuote.request_number || '';
                        this.quote.employee_id = existingQuote.employee_id || null;
                        this.quote.project_name = existingQuote.project?.name || '';
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
                        // Inicializar campos de búsqueda de cliente/subcliente
                        if (this.quote.client_id) {
                            const client = this.allClients.find(c => c.id === this.quote.client_id);
                            if (client) {
                                this.clientSearch = client.business_name;

                                // [FIX] Usar datos del subcliente precargados si existen
                                if (existingQuote.sub_client) {
                                    console.log('✅ Subcliente precargado:', existingQuote.sub_client.name);
                                    this.subClientSearch = existingQuote.sub_client.name;
                                    // Asegurar que CECO esté seteado
                                    if (!this.quote.ceco) {
                                        this.quote.ceco = existingQuote.sub_client.ceco || '';
                                    }
                                }

                                // Cargar subclientes (para el dropdown)
                                this.loadSubClients(client.id).then(() => {
                                    // Si por alguna razón no se llenó con el precargado (caso raro), intentar buscar en la lista
                                    if (this.quote.sub_client_id && !this.subClientSearch) {
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
                            // Ordenar los detalles por 'line' ascendente para respetar el orden visual
                            existingQuote.quote_details.sort((a, b) => a.line - b.line);
                            existingQuote.quote_details.forEach(detail => {
                                const item = {
                                    _uid: Math.random().toString(36).substr(2, 9), // Unique ID for Drag & Drop
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
                    } else {
                        // Si no hay existingQuote, inicializa los campos con los valores del proyecto
                        if (projectFromPHP) {
                            this.quote.client_id = projectFromPHP.client_id || null;
                            this.quote.sub_client_id = projectFromPHP.sub_client_id || null;
                            this.quote.service_code = projectFromPHP.service_code || '';
                            this.quote.request_number = projectFromPHP.service_code || '';
                            this.quote.project_id = projectFromPHP.id || null;
                        }
                    }

                    if (!existingQuote && suggestedRequestNumber) {
                        this.quote.request_number = suggestedRequestNumber;
                    }

                    // Nueva lógica: inicializar búsqueda visual si hay sub_client_id en el proyecto
                    if (!existingQuote && projectFromPHP && projectFromPHP.sub_client_id) {
                        console.log('[INIT] Buscando subcliente por ID:', projectFromPHP.sub_client_id);
                        fetch(`/api/sub-clients/${projectFromPHP.sub_client_id}`)
                            .then(res => res.json())
                            .then(subClient => {
                                console.log('[FETCH] subClient:', subClient);
                                this.quote.sub_client_id = subClient.id;
                                this.subClientSearch = subClient.name;
                                this.quote.ceco = subClient.ceco || '';
                                this.quote.client_id = subClient.client_id;
                                // Busca el cliente y setea el nombre en el buscador
                                const client = this.allClients.find(c => c.id === subClient.client_id);
                                if (client) {
                                    this.clientSearch = client.business_name;
                                    console.log('[FETCH] client.business_name:', client.business_name);
                                }
                                // Cargar subclientes del cliente para el select
                                this.loadSubClients(subClient.client_id);
                            })
                            .catch(error => {
                                console.error('[FETCH ERROR] subClient:', error);
                            });
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
                            _uid: Math.random().toString(36).substr(2, 9),
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
                        // Construir URL con parámetros de búsqueda
                        // NOTA: Eliminamos el filtro price_type_id para permitir búsqueda global
                        let url = `/api/pricelists/search?q=${encodeURIComponent(this.searchModal.query)}&limit=30`;

                        // if (this.searchModal.filter) {
                        //     url += `&price_type_id=${this.searchModal.filter}`;
                        // }

                        console.log('🔍 Searching URL:', url);

                        const response = await fetch(url);

                        if (!response.ok) {
                            console.error('❌ Network response not ok:', response.statusText);
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const text = await response.text();
                        console.log('📄 Raw response:', text);

                        try {
                            this.searchModal.results = JSON.parse(text);
                            console.log('✅ Parsed results:', this.searchModal.results);
                        } catch (e) {
                            console.error('❌ JSON Parse error:', e);
                            this.searchModal.results = [];
                        }

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

                // Cargar todos los subclientes de un cliente desde API (con búsqueda)
                async loadSubClients(clientId, search = '') {
                    this.loadingSubClients = true;
                    console.log('📦 Cargando subclientes para client_id:', clientId, 'con búsqueda:', search);

                    try {
                        let url = `/api/sub-clients?client_id=${clientId}`;
                        if (search && search.length > 0) {
                            url += `&q=${encodeURIComponent(search)}`;
                        }
                        const response = await fetch(url);
                        const data = await response.json();
                        this.subClients = data.data || data;
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

                // Filtrar subclientes localmente y remotamente mientras se escribe (con debounce)
                filterSubClients() {
                    // Limpiar timeout anterior si existe
                    if (this.subClientSearchTimeout) {
                        clearTimeout(this.subClientSearchTimeout);
                    }

                    const query = this.subClientSearch.toLowerCase().trim();
                    if (!this.quote.client_id) return;

                    // Abrir dropdown inmediatamente
                    this.subClientDropdownOpen = true;

                    // Debounce: esperar 400ms después de que el usuario termine de escribir
                    this.subClientSearchTimeout = setTimeout(async () => {
                        await this.loadSubClients(this.quote.client_id, query);
                    }, 400);
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
                        _uid: Math.random().toString(36).substr(2, 9),
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

                getTotal() {
                    return this.getSubtotal();
                },

                // Save
                async saveQuote() {
                    this.saving = true;

                    try {
                        console.log('🚀 Iniciando guardado de cotización...');

                        // Preparar datos de la cotización
                        const quoteData = {
                            request_number: this.quote.request_number,
                            project_id: this.quote.project_id,
                            employee_id: this.quote.employee_id,
                            project_name: this.quote.project_name,
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
                            // Notificación SweetAlert2
                            Swal.fire({
                                icon: 'success',
                                title: this.quote.id ? '¡Cotización actualizada!' : '¡Cotización creada!',
                                text: this.quote.id ?
                                    'La cotización se ha actualizado correctamente.' :
                                    'La cotización se ha guardado correctamente.',
                                timer: 1800,
                                showConfirmButton: false
                            });

                            // Redirigir si es creación
                            if (!this.quote.id) {
                                setTimeout(() => {
                                    window.location.href = `/dashboard/quotes/${result.id}/edit`;
                                }, 1800);
                            }
                            // Si es edición, puedes recargar datos o mantener el estado
                        } else {
                            // Mostrar errores de validación si existen
                            if (result.errors) {
                                // Unir todos los mensajes de error en un string
                                let messages = Object.values(result.errors)
                                    .flat()
                                    .join('<br>');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Errores de validación',
                                    html: messages
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Error desconocido al guardar la cotización'
                                });
                            }
                        }

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'Error de conexión al guardar la cotización'
                        });
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
                        energy_sci_manager: 'Raul Quispe',
                        ceco: '',
                        status: 'Pendiente',
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
                projectFromPHP, // <-- Añade esto para exponerlo en el template
            };
        }
    </script>
</x-filament-panels::page>
