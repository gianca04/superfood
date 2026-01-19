{{-- Quote Footer Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-footer') --}}

<div
    class="fixed bottom-0 left-0 right-0 z-50 px-6 py-4 border-t border-gray-200 shadow-lg bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm dark:border-gray-700 shadow-gray-900/10">
    <div class="flex items-center justify-between mx-auto max-w-7xl">
        {{-- Status --}}
        <div class="flex items-center gap-3">
            <span class="relative flex w-2 h-2">
                <span class="absolute w-full h-full rounded-full opacity-75 animate-ping bg-emerald-400"></span>
                <span class="relative w-2 h-2 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-sm text-gray-500" x-text="getTotalItems() + ' items'"></span>
        </div>

        {{-- Totals --}}
        <div class="flex items-center gap-8">
            <div class="text-right">
                <div class="text-xs font-medium text-gray-400">Subtotal</div>
                <div class="font-mono font-bold text-gray-700 dark:text-gray-200"
                    x-text="'S/ ' + getSubtotal().toFixed(2)"></div>
            </div>
            <div class="text-right">
                <div class="text-xs font-medium text-gray-400">IGV (18%)</div>
                <div class="font-mono font-bold text-gray-700 dark:text-gray-200" x-text="'S/ ' + getIGV().toFixed(2)">
                </div>
            </div>
            <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
            <div class="text-right">
                <div class="text-xs font-medium text-gray-400">Total</div>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"
                    x-text="'S/ ' + getTotal().toFixed(2)"></div>
            </div>

            {{-- Save Button --}}
            <button @click="saveQuote()" :disabled="saving"
                class="flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/25 transition-all hover:-translate-y-0.5 active:scale-95">
                <span x-show="!saving" class="material-symbols-outlined">save</span>
                <span x-show="saving" class="material-symbols-outlined animate-spin">progress_activity</span>
                <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
            </button>
        </div>
    </div>
</div>
