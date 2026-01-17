@vite(['resources/css/app.css', 'resources/js/app.js'])
<x-filament-panels::page>

    <body
        class="bg-background-light dark:bg-background-dark font-sans text-slate-800 dark:text-slate-200 min-h-screen flex flex-col transition-colors duration-200">
        <main class="flex-grow p-6 max-w-[1600px] mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-9 space-y-8">
                <div
                    class="bg-white dark:bg-surface-dark rounded-xl shadow-card border border-gray-200 dark:border-gray-700 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <h2
                                class="flex items-center text-sm uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold mb-4">
                                <span class="material-symbols-outlined text-lg mr-2 text-primary">domain</span>
                                Datos del Proveedor
                            </h2>
                            <div class="space-y-3">
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <label
                                        class="col-span-3 text-xs font-semibold text-slate-600 dark:text-slate-400">Servicio</label>
                                    <input
                                        class="col-span-9 form-input-clean bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800"
                                        placeholder="Ingrese nombre del servicio" type="text" />
                                </div>
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <label
                                        class="col-span-3 text-xs font-semibold text-slate-600 dark:text-slate-400">Empresa</label>
                                    <div
                                        class="col-span-9 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700">
                                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">SAT
                                            INDUSTRIALES</span>
                                        <span class="text-xs text-slate-400 font-mono">RUC: 20539249640</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <label
                                        class="col-span-3 text-xs font-semibold text-slate-600 dark:text-slate-400">Resp.</label>
                                    <div class="col-span-9 text-sm font-medium text-slate-800 dark:text-slate-200 px-2">
                                        Joel Viera
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4 border-l border-gray-100 dark:border-gray-700 pl-0 md:pl-8">
                            <h2
                                class="flex items-center text-sm uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold mb-4">
                                <span class="material-symbols-outlined text-lg mr-2 text-primary">assignment</span>
                                Detalles de Solicitud
                            </h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Cliente</label>
                                    <input
                                        class="form-input-clean font-semibold text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10 border-blue-100 dark:border-blue-900"
                                        type="text" value="Piura - PVH" />
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Fecha
                                        Ejec.</label>
                                    <input class="form-input-clean text-slate-700 dark:text-slate-300" type="date" />
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase">Categoría</label>
                                    <select class="form-input-clean text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <option>II.EE. Baja Tensión</option>
                                        <option>Mantenimiento</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">CECO</label>
                                    <input
                                        class="form-input-clean font-mono text-right text-slate-700 dark:text-slate-300"
                                        type="text" value="29150404" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-surface-dark rounded-xl shadow-card border border-gray-200 dark:border-gray-700 overflow-hidden group/card transition-all hover:shadow-soft">
                    <div class="card-header">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-primary">
                                <span class="material-symbols-outlined">flight_takeoff</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-white">Viáticos</h3>
                                <p class="text-xs text-slate-400">Gastos de traslado y alojamiento</p>
                            </div>
                        </div>
                        <div class="section-badge">
                            Subtotal: <span class="font-mono font-bold text-slate-900 dark:text-white ml-1">S/
                                83.00</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="bg-gray-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-center w-12">#</th>
                                        <th class="px-4 py-3 w-32">Código</th>
                                        <th class="px-4 py-3 min-w-[240px]">Descripción</th>
                                        <th class="px-4 py-3 w-24 text-center">Unidad</th>
                                        <th class="px-4 py-3 w-24 text-center">Cant.</th>
                                        <th class="px-4 py-3 w-32 text-right">Precio U.</th>
                                        <th class="px-6 py-3 w-32 text-right">Total</th>
                                        <th class="px-2 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                                    <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-2 text-center text-slate-400 text-xs">1</td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-mono text-xs text-slate-500" type="text"
                                                value="VABT21" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-medium text-slate-700 dark:text-slate-200"
                                                type="text" value="VIATICO CORRECTIVO DIA ADICIONAL" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center text-xs uppercase" type="text"
                                                value="P/DÍA" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/20 rounded"
                                                type="number" value="1" /></td>
                                        <td class="px-4 py-2"><input class="form-input-clean text-right font-mono"
                                                type="number" value="83.00" /></td>
                                        <td
                                            class="px-6 py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            83.00</td>
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-gray-50/50 dark:bg-slate-800 border-t border-gray-100 dark:border-gray-700">
                            <button
                                class="action-btn bg-white dark:bg-slate-700 text-primary border border-primary/20 hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 w-full justify-center border-dashed">
                                <span class="material-symbols-outlined text-xl mr-2">add</span>
                                Agregar Item de Viáticos
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-surface-dark rounded-xl shadow-card border border-gray-200 dark:border-gray-700 overflow-hidden group/card transition-all hover:shadow-soft">
                    <div class="card-header">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-primary">
                                <span class="material-symbols-outlined">inventory_2</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-white">Suministros</h3>
                                <p class="text-xs text-slate-400">Materiales y equipos</p>
                            </div>
                        </div>
                        <div class="section-badge">
                            Subtotal: <span class="font-mono font-bold text-slate-900 dark:text-white ml-1">S/
                                36.00</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="bg-gray-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-center w-12">#</th>
                                        <th class="px-4 py-3 w-32">Código</th>
                                        <th class="px-4 py-3 min-w-[240px]">Descripción</th>
                                        <th class="px-4 py-3 w-24 text-center">Unidad</th>
                                        <th class="px-4 py-3 w-24 text-center">Cant.</th>
                                        <th class="px-4 py-3 w-32 text-right">Precio U.</th>
                                        <th class="px-6 py-3 w-32 text-right">Total</th>
                                        <th class="px-2 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                                    <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-2 text-center text-slate-400 text-xs">1</td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-mono text-xs text-slate-500" type="text"
                                                value="MCBT471" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-medium text-slate-700 dark:text-slate-200"
                                                type="text" value="ROTULO PARA TÍTULO DE TABLERO" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center text-xs uppercase" type="text"
                                                value="UND" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/20 rounded"
                                                type="number" value="1" /></td>
                                        <td class="px-4 py-2"><input class="form-input-clean text-right font-mono"
                                                type="number" value="18.00" /></td>
                                        <td
                                            class="px-6 py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            18.00</td>
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-2 text-center text-slate-400 text-xs">2</td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-mono text-xs text-slate-500" type="text"
                                                value="MCBT472" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-medium text-slate-700 dark:text-slate-200"
                                                type="text" value="CINTILLOS DE NYLON NEGRO 15CM" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center text-xs uppercase" type="text"
                                                value="PQT" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/20 rounded"
                                                type="number" value="1" /></td>
                                        <td class="px-4 py-2"><input class="form-input-clean text-right font-mono"
                                                type="number" value="18.00" /></td>
                                        <td
                                            class="px-6 py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            18.00</td>
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-gray-50/50 dark:bg-slate-800 border-t border-gray-100 dark:border-gray-700">
                            <button
                                class="action-btn bg-white dark:bg-slate-700 text-primary border border-primary/20 hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 w-full justify-center border-dashed">
                                <span class="material-symbols-outlined text-xl mr-2">add</span>
                                Agregar Item de Suministros
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-surface-dark rounded-xl shadow-card border border-gray-200 dark:border-gray-700 overflow-hidden group/card transition-all hover:shadow-soft">
                    <div class="card-header">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-primary">
                                <span class="material-symbols-outlined">engineering</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-white">Mano de Obra</h3>
                                <p class="text-xs text-slate-400">Personal técnico y especializado</p>
                            </div>
                        </div>
                        <div class="section-badge">
                            Subtotal: <span class="font-mono font-bold text-slate-900 dark:text-white ml-1">S/
                                36.00</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="bg-gray-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-center w-12">#</th>
                                        <th class="px-4 py-3 w-32">Código</th>
                                        <th class="px-4 py-3 min-w-[240px]">Descripción</th>
                                        <th class="px-4 py-3 w-24 text-center">Unidad</th>
                                        <th class="px-4 py-3 w-24 text-center">Cant.</th>
                                        <th class="px-4 py-3 w-32 text-right">Precio U.</th>
                                        <th class="px-6 py-3 w-32 text-right">Total</th>
                                        <th class="px-2 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                                    <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-2 text-center text-slate-400 text-xs">1</td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-mono text-xs text-slate-500" type="text"
                                                value="MO-001" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-medium text-slate-700 dark:text-slate-200"
                                                type="text" value="TECNICO ELECTRICISTA NIVEL 1" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center text-xs uppercase" type="text"
                                                value="HH" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/20 rounded"
                                                type="number" value="1" /></td>
                                        <td class="px-4 py-2"><input class="form-input-clean text-right font-mono"
                                                type="number" value="18.00" /></td>
                                        <td
                                            class="px-6 py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            18.00</td>
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-2 text-center text-slate-400 text-xs">2</td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-mono text-xs text-slate-500" type="text"
                                                value="MO-002" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean font-medium text-slate-700 dark:text-slate-200"
                                                type="text" value="AYUDANTE TECNICO" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center text-xs uppercase" type="text"
                                                value="HH" /></td>
                                        <td class="px-4 py-2"><input
                                                class="form-input-clean text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/20 rounded"
                                                type="number" value="1" /></td>
                                        <td class="px-4 py-2"><input class="form-input-clean text-right font-mono"
                                                type="number" value="18.00" /></td>
                                        <td
                                            class="px-6 py-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            18.00</td>
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-gray-50/50 dark:bg-slate-800 border-t border-gray-100 dark:border-gray-700">
                            <button
                                class="action-btn bg-white dark:bg-slate-700 text-primary border border-primary/20 hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 w-full justify-center border-dashed">
                                <span class="material-symbols-outlined text-xl mr-2">add</span>
                                Agregar Item de Mano de Obra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-3 space-y-6">
                <aside class="sticky top-24 space-y-6">
                    <div
                        class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-6">
                            Resumen
                            Financiero</h3>
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                                <span class="font-mono font-medium text-slate-700 dark:text-slate-300">S/ 155.00</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Descuento (0%)</span>
                                <span class="font-mono font-medium text-slate-700 dark:text-slate-300">-</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">IGV (18%)</span>
                                <span class="font-mono font-medium text-slate-700 dark:text-slate-300">S/ 27.90</span>
                            </div>
                            <div class="h-px bg-gray-100 dark:bg-gray-700 my-2"></div>
                            <div class="flex justify-between items-end">
                                <span class="font-bold text-slate-900 dark:text-white">Total</span>
                                <div class="text-right">
                                    <span
                                        class="block text-2xl font-bold text-emerald-600 dark:text-emerald-400 leading-none">S/
                                        182.90</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Incluye IGV</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button
                                class="action-btn w-full justify-center bg-primary hover:bg-primary-dark text-white shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5">
                                <span class="material-symbols-outlined text-lg mr-2">save</span>
                                Guardar Cotización
                            </button>
                            <button
                                class="action-btn w-full justify-center bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-slate-600">
                                <span class="material-symbols-outlined text-lg mr-2">print</span>
                                Vista Previa / Imprimir
                            </button>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                            Estado del Documento</h3>
                        <div class="flex items-center mb-4">
                            <span class="flex h-3 w-3 relative mr-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                            </span>
                            <span class="font-semibold text-slate-800 dark:text-white">Borrador</span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                            <p>Creado: 24 Oct 2023</p>
                            <p>Última mod: Hace 5 min</p>
                            <p>Por: Joel Viera</p>
                        </div>
                    </div>
                </aside>
            </div>
        </main>

    </body>
</x-filament-panels::page>