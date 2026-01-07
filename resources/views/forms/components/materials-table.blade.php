@vite(['resources/css/app.css'])

@php
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="materialsTableComponent($wire.entangle('{{ $getStatePath() }}'), {{ $isDisabled ? 'true' : 'false' }})"
        wire:ignore
    >
        <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-600">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Materiales/Herramientas
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Unidad
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Cantidad
                        </th>
                        @if(!$isDisabled)
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-24">
                            Acciones
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(row, index) in rows" :key="index">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3">
                                <input 
                                    type="text" 
                                    x-model="row.material"
                                    @input="updateState()"
                                    :disabled="isDisabled"
                                    placeholder="Ej: Cemento"
                                    class="w-full min-w-[200px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:bg-gray-100 disabled:dark:bg-gray-700 disabled:cursor-not-allowed"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <input 
                                    type="text" 
                                    x-model="row.unidad"
                                    @input="updateState()"
                                    :disabled="isDisabled"
                                    placeholder="Ej: sacos"
                                    class="w-full min-w-[120px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:bg-gray-100 disabled:dark:bg-gray-700 disabled:cursor-not-allowed"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <input 
                                    type="text" 
                                    x-model="row.cantidad"
                                    @input="updateState()"
                                    :disabled="isDisabled"
                                    placeholder="Ej: 10"
                                    class="w-full min-w-[100px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:bg-gray-100 disabled:dark:bg-gray-700 disabled:cursor-not-allowed"
                                />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center" x-show="!isDisabled">
                                <button 
                                    type="button"
                                    @click="removeRow(index)"
                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                    title="Eliminar fila"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    
                    <!-- Empty state -->
                    <tr x-show="rows.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm">No hay materiales agregados</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add row button -->
        <div class="mt-3" x-show="!isDisabled">
            <button 
                type="button"
                @click="addRow()"
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Agregar material
            </button>
        </div>
    </div>
</x-dynamic-component>

<script>
function materialsTableComponent(stateEntangle, isDisabled = false) {
    return {
        state: stateEntangle,
        rows: [],
        isDisabled: isDisabled,
        
        init() {
            // Inicializar con datos existentes o array vacío
            this.syncFromState();
            
            // Si no hay filas, agregar una por defecto
            if (this.rows.length === 0) {
                this.rows.push({
                    material: '',
                    unidad: '',
                    cantidad: ''
                });
            }
            
            // Watch para cambios externos
            this.$watch('state', (value) => {
                if (Array.isArray(value) && JSON.stringify(value) !== JSON.stringify(this.rows)) {
                    this.syncFromState();
                }
            });
        },
        
        syncFromState() {
            if (Array.isArray(this.state) && this.state.length > 0) {
                // Hacer una copia profunda para evitar referencias
                this.rows = JSON.parse(JSON.stringify(this.state));
            } else {
                this.rows = [];
            }
        },
        
        addRow() {
            this.rows.push({
                material: '',
                unidad: '',
                cantidad: ''
            });
            // No actualizar state al agregar fila vacía
        },
        
        removeRow(index) {
            this.rows.splice(index, 1);
            this.updateState();
        },
        
        updateState() {
            // Crear copia de todas las filas (incluyendo las que tienen al menos un valor)
            const allRows = this.rows.map(row => ({
                material: row.material || '',
                unidad: row.unidad || '',
                cantidad: row.cantidad || ''
            }));
            
            // Filtrar filas completamente vacías para el estado
            const validRows = allRows.filter(row => 
                row.material.trim() !== '' || 
                row.unidad.trim() !== '' || 
                row.cantidad.trim() !== ''
            );
            
            console.log('Materiales guardando:', JSON.stringify(validRows, null, 2));
            this.state = validRows;
        }
    }
}
</script>
