{{-- Quote Section Card Component --}}
{{-- Usage: Template inside x-for loop --}}
{{-- This is the template content, included in main view --}}

<div class="card-section">
    {{-- Header --}}
    <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <span class="text-base material-symbols-outlined" :class="section.iconClass" x-text="section.icon"></span>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white" x-text="section.title"></h3>
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
        <table class="w-full text-xs border-collapse">
            <thead class="border-b border-gray-200 select-none bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700">
                <tr class="text-gray-500 uppercase dark:text-gray-400">
                    {{-- # --}}
                    <th class="w-10 px-2 py-2 text-center border-r border-gray-200 dark:border-gray-700">#</th>

                    {{-- Línea --}}
                    <th class="relative px-2 py-2 text-left border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.code + 'px' }">
                        Línea
                        <div @mousedown.prevent.stop="startResize('code', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Descripción --}}
                    <th class="relative px-2 py-2 text-left border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.description + 'px' }">
                        Descripción
                        <div @mousedown.prevent.stop="startResize('description', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Comentario --}}
                    <th class="relative px-2 py-2 text-left border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.comment + 'px' }">
                        Comentario
                        <div @mousedown.prevent.stop="startResize('comment', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Unid. --}}
                    <th class="relative px-2 py-2 text-center border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.unit + 'px' }">
                        Unid.
                        <div @mousedown.prevent.stop="startResize('unit', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Cant. --}}
                    <th class="relative px-2 py-2 text-center border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.quantity + 'px' }">
                        Cant.
                        <div @mousedown.prevent.stop="startResize('quantity', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- P.U. --}}
                    <th class="relative px-2 py-2 text-right border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.unit_price + 'px' }">
                        P.U.
                        <div @mousedown.prevent.stop="startResize('unit_price', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Subtotal --}}
                    <th class="relative px-2 py-2 text-right border-r border-gray-200 group dark:border-gray-700"
                        :style="{ width: columnWidths.subtotal + 'px' }">
                        Subtotal
                        <div @mousedown.prevent.stop="startResize('subtotal', $event)"
                            class="absolute top-0 bottom-0 right-0 w-1 transition-colors cursor-col-resize hover:bg-emerald-500 group-hover:bg-gray-300">
                        </div>
                    </th>

                    {{-- Actions --}}
                    <th class="w-8 px-2 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                <template x-for="(item, index) in items[section.key]" :key="index">
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 group">

                        {{-- # --}}
                        <td class="px-2 py-2 text-center text-gray-400 align-top border-r border-gray-100 dark:border-gray-700/50"
                            x-text="index + 1"></td>

                        {{-- Línea --}}
                        <td class="px-2 py-2 align-top border-r border-gray-100 dark:border-gray-700/50">
                            <span
                                class="font-mono text-xs px-1 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 break-all"
                                x-text="item.code"></span>
                        </td>

                        {{-- Descripción (Textarea) --}}
                        <td class="px-1 py-1 align-top border-r border-gray-100 dark:border-gray-700/50">
                            <textarea x-model="item.description" rows="1"
                                class="w-full px-1 py-1 text-xs border-0 bg-transparent focus:ring-1 focus:ring-emerald-500 rounded resize-vertical min-h-[28px]"
                                style="field-sizing: content; min-height: 1.5lh;"></textarea>
                        </td>

                        {{-- Comentario (Textarea) --}}
                        <td class="px-1 py-1 align-top border-r border-gray-100 dark:border-gray-700/50">
                            <textarea x-model="item.comment" rows="1" placeholder="—"
                                class="w-full px-1 py-1 text-xs border-0 bg-transparent text-gray-500 focus:ring-1 focus:ring-emerald-500 rounded resize-vertical min-h-[28px]"
                                style="field-sizing: content; min-height: 1.5lh;"></textarea>
                        </td>

                        {{-- Unid. --}}
                        <td class="px-2 py-2 text-center text-gray-400 uppercase align-top border-r border-gray-100 dark:border-gray-700/50"
                            x-text="item.unit"></td>

                        {{-- Cant. --}}
                        <td class="px-1 py-1 align-top border-r border-gray-100 dark:border-gray-700/50">
                            <input x-model.number="item.quantity" @input="recalculate()" type="number" min="0.01"
                                step="0.01"
                                class="w-full px-1 py-1 text-xs font-medium text-center text-blue-600 border border-gray-200 rounded dark:border-gray-600 bg-blue-50/50 dark:bg-blue-900/20 dark:text-blue-400" />
                        </td>

                        {{-- P.U. --}}
                        <td class="px-1 py-1 align-top border-r border-gray-100 dark:border-gray-700/50">
                            <input x-model.number="item.unit_price" @input="recalculate()" type="number" min="0"
                                step="0.01"
                                class="w-full px-1 py-1 font-mono text-xs text-right border border-gray-200 rounded dark:border-gray-600" />
                        </td>

                        {{-- Subtotal --}}
                        <td class="px-2 py-2 font-mono font-semibold text-right text-gray-900 align-top border-r border-gray-100 dark:text-white dark:border-gray-700/50"
                            x-text="(item.quantity * item.unit_price).toFixed(2)"></td>

                        {{-- Actions --}}
                        <td class="px-1 py-1 text-center align-top">
                            <button @click="removeItem(section.key, index)"
                                class="p-1 text-gray-400 rounded opacity-0 group-hover:opacity-100 hover:text-red-500 hover:bg-red-50">
                                <span class="text-sm material-symbols-outlined">close</span>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Empty State --}}
        <div x-show="items[section.key].length === 0" class="py-6 text-center text-gray-400">
            <span class="block mb-1 text-3xl opacity-50 material-symbols-outlined">inventory_2</span>
            <p class="text-xs">No hay items</p>
        </div>
    </div>

    {{-- Add Button --}}
    <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-700">
        <button @click="openSearchModal(section.key)"
            class="flex items-center gap-1 text-xs text-gray-500 transition-colors hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400">
            <span class="text-sm material-symbols-outlined">add</span>
            <span x-text="'Agregar item'"></span>
        </button>
    </div>
</div>
