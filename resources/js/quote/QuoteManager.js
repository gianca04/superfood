import { pricelistService } from './PricelistService.js';

/**
 * QuoteManager Alpine.js Component
 * Manages quote form state and interactions
 */
export function QuoteManager() {
    return {
        // Quote header data
        quote: {
            id: null,
            service_name: '',
            client_name: 'Piura - PVH',
            execution_date: '',
            ceco: '29150404',
        },

        // Section configurations
        sections: [
            {
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
            mano_obra: [],
        },

        // Search modal state
        searchModal: {
            open: false,
            section: null,
            query: '',
            results: [],
            loading: false,
            filter: null,
        },

        // Price types for filter
        priceTypes: [
            { id: 1, name: 'Mantenimiento Preventivo BT', shortName: 'Preventivo' },
            { id: 2, name: 'Mantenimiento Correctivos BT', shortName: 'Correctivo' },
            { id: 3, name: 'Viáticos correctivos BT', shortName: 'Viáticos' },
        ],

        // UI state
        saving: false,
        igvRate: 0.18,

        // ===== Modal Methods =====

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

        // ===== Search Methods =====

        async searchPricelist() {
            if (this.searchModal.query.length < 2) {
                this.searchModal.results = [];
                return;
            }

            this.searchModal.loading = true;

            try {
                this.searchModal.results = await pricelistService.search(
                    this.searchModal.query,
                    this.searchModal.filter,
                    30
                );
            } catch (error) {
                console.error('Search error:', error);
                this.searchModal.results = [];
            } finally {
                this.searchModal.loading = false;
            }
        },

        // ===== Item Management =====

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

        removeItem(sectionKey, index) {
            this.items[sectionKey].splice(index, 1);
        },

        // ===== Calculations =====

        recalculate() {
            // Alpine.js reactivity handles this
        },

        getSectionSubtotal(sectionKey) {
            return this.items[sectionKey].reduce((sum, item) => {
                return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            }, 0);
        },

        getTotalItems() {
            return Object.values(this.items).reduce((sum, arr) => sum + arr.length, 0);
        },

        getSubtotal() {
            return this.sections.reduce((sum, section) => sum + this.getSectionSubtotal(section.key), 0);
        },

        getIGV() {
            return this.getSubtotal() * this.igvRate;
        },

        getTotal() {
            return this.getSubtotal() + this.getIGV();
        },

        // ===== Persistence =====

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

            const payload = { quote: this.quote, details };
            console.log('Saving quote:', payload);

            // TODO: Implement actual API save
            await new Promise(r => setTimeout(r, 1000));

            this.saving = false;
            alert('¡Cotización guardada! (Demo)');
        },
    };
}

// Register Alpine component globally
document.addEventListener('alpine:init', () => {
    Alpine.data('quoteManager', QuoteManager);
});

export default QuoteManager;
