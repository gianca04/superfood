{{-- Quote Section Card Component --}}
{{-- Usage: Template inside x-for loop --}}
{{-- This is the template content, included in main view --}}

<div class="card-section">
    {{-- Header --}}
    <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base" :class="section.iconClass" x-text="section.icon"></span>
            <h3 class="font-semibold text-sm text-gray-800 dark:text-white" x-text="section.title"></h3>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400" x-text="items[section.key].length + ' items'"></span>
            <span
                class="text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded"
                x-text="'S/ ' + getSectionSubtotal(section.key).toFixed(2)"></span>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <tr class="text-gray-500 dark:text-gray-400 uppercase">
                    <th class="px-2 py-2 text-center w-10">#</th>
                    <th class="px-2 py-2 text-left w-20">Línea</th>
                    <th class="px-2 py-2 text-left min-w-[200px]">Descripción</th>
                    <th class="px-2 py-2 text-left min-w-[120px]">Comentario</th>
                    <th class="px-2 py-2 text-center w-16">Unid.</th>
                    <th class="px-2 py-2 text-center w-16">Cant.</th>
                    <th class="px-2 py-2 text-right w-20">P.U.</th>
                    <th class="px-2 py-2 text-right w-24">Subtotal</th>
                    <th class="px-2 py-2 w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                <template x-for="(item, index) in items[section.key]" :key="index">
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 group">
                        <td class="px-2 py-1 text-center text-gray-400" x-text="index + 1"></td>
                        <td class="px-2 py-1">
                            <span
                                class="font-mono text-xs px-1 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400"
                                x-text="item.code"></span>
                        </td>
                        <td class="px-1 py-1">
                            <input x-model="item.description" type="text"
                                class="w-full px-1 py-0.5 text-xs border-0 bg-transparent focus:ring-1 focus:ring-emerald-500 rounded" />
                        </td>
                        <td class="px-1 py-1">
                            <input x-model="item.comment" type="text" placeholder="—"
                                class="w-full px-1 py-0.5 text-xs border-0 bg-transparent text-gray-500 focus:ring-1 focus:ring-emerald-500 rounded" />
                        </td>
                        <td class="px-2 py-1 text-center text-gray-400 uppercase" x-text="item.unit"></td>
                        <td class="px-1 py-1">
                            <input x-model.number="item.quantity" @input="recalculate()" type="number" min="0.01"
                                step="0.01"
                                class="w-full px-1 py-0.5 text-xs text-center border border-gray-200 dark:border-gray-600 rounded bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium" />
                        </td>
                        <td class="px-1 py-1">
                            <input x-model.number="item.unit_price" @input="recalculate()" type="number" min="0"
                                step="0.01"
                                class="w-full px-1 py-0.5 text-xs text-right border border-gray-200 dark:border-gray-600 rounded font-mono" />
                        </td>
                        <td class="px-2 py-1 text-right font-mono font-semibold text-gray-900 dark:text-white"
                            x-text="(item.quantity * item.unit_price).toFixed(2)"></td>
                        <td class="px-1 py-1 text-center">
                            <button @click="removeItem(section.key, index)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 p-0.5">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Empty State --}}
        <div x-show="items[section.key].length === 0" class="py-6 text-center text-gray-400">
            <span class="material-symbols-outlined text-3xl mb-1 block opacity-50">inventory_2</span>
            <p class="text-xs">No hay items</p>
        </div>
    </div>

    {{-- Add Button --}}
    <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-700">
        <button @click="openSearchModal(section.key)"
            class="flex items-center gap-1 text-xs text-gray-500 hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400 transition-colors">
            <span class="material-symbols-outlined text-sm">add</span>
            <span x-text="'Agregar item'"></span>
        </button>
    </div>
</div>