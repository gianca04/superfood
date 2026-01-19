{{-- Quote Footer Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-footer') --}}

<div
    class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 shadow-lg shadow-gray-900/10 px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        {{-- Status --}}
        <div class="flex items-center gap-3">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-sm text-gray-500" x-text="getTotalItems() + ' items'"></span>
        </div>

        {{-- Totals --}}
        <div class="flex items-center gap-8">
            <div class="text-right">
                <div class="text-xs text-gray-400 font-medium">Subtotal</div>
                <div class="font-mono font-bold text-gray-700 dark:text-gray-200"
                    x-text="'S/ ' + getSubtotal().toFixed(2)"></div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400 font-medium">IGV (18%)</div>
                <div class="font-mono font-bold text-gray-700 dark:text-gray-200" x-text="'S/ ' + getIGV().toFixed(2)">
                </div>
            </div>
            <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
            <div class="text-right">
                <div class="text-xs text-gray-400 font-medium">Total</div>
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