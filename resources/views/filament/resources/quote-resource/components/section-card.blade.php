{{-- Quote Section Card Component --}}
{{-- Usage: Template inside x-for loop --}}
{{-- This is the template content, included in main view --}}

<div class="card-section">
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="section.bgClass">
                <span class="material-symbols-outlined text-xl" :class="section.iconClass" x-text="section.icon"></span>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-white" x-text="section.title"></h3>
                <p class="text-xs text-gray-400" x-text="section.subtitle"></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400" x-text="items[section.key].length + ' items'"></span>
            <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200"
                    x-text="'S/ ' + getSectionSubtotal(section.key).toFixed(2)"></span>
            </div>
        </div>
    </div>

    {{-- Items List --}}
    <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
        <template x-for="(item, index) in items[section.key]" :key="index">
            <div
                class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 group transition-colors">
                <span class="text-xs text-gray-400 w-6 text-center" x-text="index + 1"></span>
                <div class="flex-1 grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-2">
                        <span
                            class="font-mono text-xs px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-semibold"
                            x-text="item.code"></span>
                    </div>
                    <div class="col-span-4">
                        <input x-model="item.description"
                            class="form-input-clean text-sm text-gray-700 dark:text-gray-200" type="text" />
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="text-xs text-gray-400 uppercase" x-text="item.unit"></span>
                    </div>
                    <div class="col-span-2">
                        <input x-model.number="item.quantity" @input="recalculate()"
                            class="form-input-clean text-center font-semibold text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20 rounded-lg w-full"
                            type="number" min="0.01" step="0.01" />
                    </div>
                    <div class="col-span-2">
                        <input x-model.number="item.unit_price" @input="recalculate()"
                            class="form-input-clean text-right font-mono text-sm" type="number" min="0" step="0.01" />
                    </div>
                    <div class="col-span-1 text-right">
                        <span class="font-mono font-bold text-gray-900 dark:text-white text-sm"
                            x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                    </div>
                </div>
                <button @click="removeItem(section.key, index)"
                    class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-all p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="items[section.key].length === 0" class="py-8 text-center text-gray-400">
            <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">inventory_2</span>
            <p class="text-sm">No hay items agregados</p>
        </div>
    </div>

    {{-- Add Button --}}
    <div class="px-5 py-3 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
        <button @click="openSearchModal(section.key)"
            class="w-full flex items-center justify-center gap-2 py-2.5 text-emerald-600 hover:text-emerald-700 font-medium text-sm rounded-lg border-2 border-dashed border-emerald-200 dark:border-emerald-800 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 transition-all">
            <span class="material-symbols-outlined text-xl">add_circle</span>
            <span x-text="'Agregar ' + section.title"></span>
        </button>
    </div>
</div>