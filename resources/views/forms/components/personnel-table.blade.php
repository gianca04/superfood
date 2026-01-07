@vite(['resources/css/app.css'])


@php
    $isDisabled = $isDisabled();
    $employees = \App\Models\Employee::where('active', true)
        ->orderBy('first_name')
        ->get()
        ->map(fn($e) => [
            'id' => $e->id,
            'name' => $e->first_name . ' ' . $e->last_name,
            'position_id' => $e->position_id,
        ]);
    $positions = \App\Models\Position::orderBy('name')->pluck('name', 'id');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="personnelTableComponent(
            $wire.entangle('{{ $getStatePath() }}'), 
            {{ $isDisabled ? 'true' : 'false' }},
            {{ $employees->toJson() }},
            {{ $positions->toJson() }}
        )"
        wire:ignore
    >
        <div class="rounded-lg border border-gray-300 dark:border-gray-600">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Personal que realizó el trabajo
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-24">
                            H.H
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Cargo
                        </th>
                        @if(!$isDisabled)
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-24">
                            Acciones
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700" style="overflow: visible;">
                    <template x-for="(row, index) in rows" :key="index">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3" style="overflow: visible;">
                                <!-- Custom Select con búsqueda -->
                                <div 
                                    x-data="{ 
                                        open: false, 
                                        search: '',
                                        get filteredEmployees() {
                                            if (!this.search) return employees;
                                            return employees.filter(emp => 
                                                emp.name.toLowerCase().includes(this.search.toLowerCase())
                                            );
                                        },
                                        get selectedEmployee() {
                                            return employees.find(e => e.id == row.employee_id);
                                        },
                                        selectEmployee(emp) {
                                            row.employee_id = emp.id;
                                            onEmployeeChange(index);
                                            updateState();
                                            this.open = false;
                                            this.search = '';
                                        },
                                        clearSelection() {
                                            row.employee_id = '';
                                            onEmployeeChange(index);
                                            updateState();
                                        }
                                    }"
                                    class="relative w-full min-w-[280px]"
                                    @click.away="open = false"
                                >
                                    <!-- Trigger Button -->
                                    <button
                                        type="button"
                                        @click="if (!isDisabled) open = !open"
                                        :disabled="isDisabled"
                                        class="relative w-full cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-3 pr-10 text-left shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 text-sm transition-all duration-200 disabled:bg-gray-100 disabled:dark:bg-gray-700 disabled:cursor-not-allowed hover:border-gray-400 dark:hover:border-gray-500"
                                        :class="{ 'ring-2 ring-primary-500 border-primary-500': open }"
                                    >
                                        <span class="flex items-center">
                                            <template x-if="selectedEmployee">
                                                <span class="flex items-center gap-2">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-primary-700 dark:text-primary-300" x-text="selectedEmployee.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase()"></span>
                                                    </span>
                                                    <span class="block truncate text-gray-900 dark:text-gray-100" x-text="selectedEmployee.name"></span>
                                                </span>
                                            </template>
                                            <template x-if="!selectedEmployee">
                                                <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    Seleccionar empleado...
                                                </span>
                                            </template>
                                        </span>
                                        <!-- Clear button -->
                                        <span 
                                            x-show="selectedEmployee && !isDisabled" 
                                            @click.stop="clearSelection()"
                                            class="absolute inset-y-0 right-8 flex items-center pr-2 cursor-pointer hover:text-red-500 text-gray-400"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                        <!-- Dropdown icon -->
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Dropdown Panel -->
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute mt-1 w-full rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                        style="display: none; z-index: 9999;"
                                    >
                                        <!-- Search Input -->
                                        <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                            <div class="relative">
                                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <input
                                                    type="text"
                                                    x-model="search"
                                                    @click.stop
                                                    placeholder="Buscar empleado..."
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 pl-9 pr-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
                                                    x-ref="searchInput"
                                                />
                                            </div>
                                        </div>

                                        <!-- Options List -->
                                        <ul class="max-h-60 overflow-auto py-1 text-sm">
                                            <template x-for="emp in filteredEmployees" :key="emp.id">
                                                <li
                                                    @click="selectEmployee(emp)"
                                                    class="relative cursor-pointer select-none py-2.5 px-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                                    :class="{ 'bg-primary-50 dark:bg-primary-900/30': row.employee_id == emp.id }"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300" x-text="emp.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase()"></span>
                                                        </span>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-gray-900 dark:text-gray-100 font-medium truncate" x-text="emp.name"></p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="positions[emp.position_id] || 'Sin cargo'"></p>
                                                        </div>
                                                        <svg x-show="row.employee_id == emp.id" class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </li>
                                            </template>
                                            <!-- Empty State -->
                                            <li x-show="filteredEmployees.length === 0" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">
                                                <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-sm">No se encontraron resultados</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <input 
                                    type="number" 
                                    x-model="row.hh"
                                    @input="updateState()"
                                    :disabled="isDisabled"
                                    placeholder="0"
                                    min="0"
                                    step="0.5"
                                    class="w-full min-w-[80px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:bg-gray-100 disabled:dark:bg-gray-700 disabled:cursor-not-allowed"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <span 
                                    x-text="getPositionName(row.employee_id)"
                                    class="inline-block w-full min-w-[200px] px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm"
                                ></span>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-sm">No hay personal agregado</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800" x-show="rows.length > 0">
                    <tr>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                            Total H.H:
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">
                            <span x-text="totalHH"></span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
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
                Agregar personal
            </button>
        </div>
    </div>
</x-dynamic-component>

<script>
function personnelTableComponent(stateEntangle, isDisabled = false, employees = [], positions = {}) {
    return {
        state: stateEntangle,
        rows: [],
        isDisabled: isDisabled,
        employees: employees,
        positions: positions,
        
        get totalHH() {
            return this.rows.reduce((sum, row) => {
                const hh = parseFloat(row.hh) || 0;
                return sum + hh;
            }, 0).toFixed(1);
        },
        
        init() {
            // Inicializar con datos existentes o array vacío
            this.syncFromState();
            
            // Si no hay filas, agregar una por defecto (solo si no está deshabilitado)
            if (this.rows.length === 0 && !this.isDisabled) {
                this.rows.push({
                    employee_id: '',
                    hh: '',
                    position_id: ''
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
        
        onEmployeeChange(index) {
            const employeeId = this.rows[index].employee_id;
            if (employeeId) {
                // Buscar el empleado y auto-llenar el cargo
                const employee = this.employees.find(e => e.id == employeeId);
                if (employee && employee.position_id) {
                    this.rows[index].position_id = employee.position_id.toString();
                }
            } else {
                this.rows[index].position_id = '';
            }
        },
        
        getPositionName(employeeId) {
            if (!employeeId) return 'Sin cargo asignado';
            const employee = this.employees.find(e => e.id == employeeId);
            if (employee && employee.position_id) {
                return this.positions[employee.position_id] || 'Sin cargo';
            }
            return 'Sin cargo';
        },
        
        addRow() {
            this.rows.push({
                employee_id: '',
                hh: '',
                position_id: ''
            });
            // No actualizar state al agregar fila vacía
        },
        
        removeRow(index) {
            this.rows.splice(index, 1);
            this.updateState();
        },
        
        updateState() {
            // Crear copia de todas las filas
            const allRows = this.rows.map(row => ({
                employee_id: row.employee_id || '',
                hh: row.hh || '',
                position_id: row.position_id || ''
            }));
            
            // Filtrar filas completamente vacías para el estado
            const validRows = allRows.filter(row => 
                row.employee_id !== '' || 
                row.hh !== '' || 
                row.position_id !== ''
            );
            
            console.log('Personal guardando:', JSON.stringify(validRows, null, 2));
            this.state = validRows;
        }
    }
}
</script>
