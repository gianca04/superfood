{{-- Quote Footer Component --}}
{{-- Usage: @include('filament.resources.quote-resource.components.quote-footer') --}}

<div
    class="sticky bottom-6 z-40 mx-auto w-[98%] max-w-7xl px-6 py-4 border border-gray-200 bg-white/80 backdrop-blur-xl dark:bg-gray-800/80 dark:border-gray-700 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] ring-1 ring-black/5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Status --}}
        <div class="flex items-center justify-between sm:justify-start gap-4">
            <div class="flex items-center gap-2">
                <span class="relative flex w-2.5 h-2.5">
                    <span class="absolute w-full h-full rounded-full opacity-75 animate-ping bg-emerald-400"></span>
                    <span class="relative w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-xs font-medium text-gray-500" x-text="getTotalItems() + ' items'"></span>
            </div>

            {{-- Mobile Totals (Visible only on very small screens) --}}
            <div class="sm:hidden text-right">
                <div class="text-[10px] uppercase tracking-wider text-gray-400">Total</div>
                <div class="text-base font-bold text-emerald-600 dark:text-emerald-400"
                    x-text="'S/ ' + getTotal().toFixed(2)"></div>
            </div>
        </div>

        {{-- Totals y Save --}}
        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-x-6 gap-y-2">


            <div class="hidden sm:block text-right">
                <div class="text-[10px] uppercase tracking-wider text-gray-400">Total</div>
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400"
                    x-text="'S/ ' + getTotal().toFixed(2)"></div>
            </div>

            {{-- Save Button --}}
            <button @click="saveQuote()" :disabled="saving"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                <span x-show="!saving" class="material-symbols-outlined text-lg">save</span>
                <span x-show="saving" class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
            </button>
        </div>
    </div>
</div>