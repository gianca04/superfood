<x-filament-panels::page>
    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/quote-form.css', 'resources/js/app.js'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Main Container with Alpine --}}
    <div x-data="quoteManager()" class="space-y-6">
        {{-- Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Sidebar --}}
            @include('filament.resources.quote-resource.components.quote-sidebar')

            {{-- Main Content --}}
            <main class="lg:col-span-9 space-y-5">
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
                    client_name: 'Piura - PVH',
                    execution_date: '',
                    ceco: '29150404',
                },

                // Sections
                sections: [
                    { key: 'viaticos', title: 'Viáticos', subtitle: 'Gastos de traslado', icon: 'flight_takeoff', priceTypeId: 3, bgClass: 'bg-blue-100 dark:bg-blue-900/30', iconClass: 'text-blue-600 dark:text-blue-400' },
                    { key: 'suministros', title: 'Suministros', subtitle: 'Materiales y equipos', icon: 'inventory_2', priceTypeId: 2, bgClass: 'bg-amber-100 dark:bg-amber-900/30', iconClass: 'text-amber-600 dark:text-amber-400' },
                    { key: 'mano_obra', title: 'Mano de Obra', subtitle: 'Personal técnico', icon: 'engineering', priceTypeId: 2, bgClass: 'bg-purple-100 dark:bg-purple-900/30', iconClass: 'text-purple-600 dark:text-purple-400' },
                ],

                // Items per section
                items: { viaticos: [], suministros: [], mano_obra: [] },

                // Modal state
                searchModal: {
                    open: false,
                    section: null,
                    query: '',
                    results: [],
                    loading: false,
                    filter: null,
                },

                // Price types
                priceTypes: [
                    { id: 1, name: 'Mantenimiento Preventivo BT', shortName: 'Preventivo' },
                    { id: 2, name: 'Mantenimiento Correctivos BT', shortName: 'Correctivo' },
                    { id: 3, name: 'Viáticos correctivos BT', shortName: 'Viáticos' },
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

                // Search
                async searchPricelist() {
                    if (this.searchModal.query.length < 2) {
                        this.searchModal.results = [];
                        return;
                    }
                    this.searchModal.loading = true;
                    const filterParam = this.searchModal.filter ? `&price_type_id=${this.searchModal.filter}` : '';
                    try {
                        const response = await fetch(`/api/pricelists/search?q=${encodeURIComponent(this.searchModal.query)}${filterParam}&limit=30`);
                        this.searchModal.results = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchModal.results = [];
                    } finally {
                        this.searchModal.loading = false;
                    }
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

                getIGV() { return this.getSubtotal() * this.igvRate; },
                getTotal() { return this.getSubtotal() + this.getIGV(); },

                // Save
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