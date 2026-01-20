{{-- Quote Footer Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-footer') --}}

<div class="sticky bottom-0 z-40 px-4 py-2 border-t border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-between">
        {{-- Status --}}
        <div class="flex items-center gap-2">
            <span class="relative flex w-2 h-2">
                <span class="absolute w-full h-full rounded-full opacity-75 animate-ping bg-emerald-400"></span>
                <span class="relative w-2 h-2 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-xs text-gray-500" x-text="getTotalItems() + ' items'"></span>
        </div>

        {{-- Totals y Save --}}
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-xs text-gray-400">Subtotal</div>
                <div class="font-mono text-sm font-semibold text-gray-700 dark:text-gray-200"
                    x-text="'S/ ' + getSubtotal().toFixed(2)"></div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400">IGV (18%)</div>
                <div class="font-mono text-sm font-semibold text-gray-700 dark:text-gray-200"
                    x-text="'S/ ' + getIGV().toFixed(2)"></div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400">Total</div>
                <div class="font-mono text-lg font-bold text-emerald-600 dark:text-emerald-400"
                    x-text="'S/ ' + getTotal().toFixed(2)"></div>
            </div>

            {{-- Save Button --}}
            <button @click="saveQuote()" :disabled="saving"
                class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-lg text-sm font-semibold transition-colors">
                <span x-show="!saving" class="material-symbols-outlined text-base">save</span>
                <span x-show="saving" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
            </button>
        </div>
    </div>
</div>