<?php

use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\VisitReportPdfController;
use App\Http\Controllers\WorkReportExcelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvidenceReportController;
use App\Http\Controllers\QuoteExportController;
use App\Http\Controllers\QuoteWarehouseController;
use App\Http\Controllers\WorkReportConsolidatedController;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use App\Http\Controllers\WorkReportWordController;
use App\Http\Controllers\RequestConsolidatedController;
use App\Http\Controllers\WorkReportController;

// Redirigir la raíz al dashboard de Filament
Route::redirect('/', '/dashboard');

// Ruta para generar Informe de Evidencias (PDF con fotos)
Route::get('/evidence-report/{workReport}/pdf', [EvidenceReportController::class, 'generateReport'])
    ->name('evidence-report.pdf')
    ->middleware('auth');

// Ruta para generar reporte PDF de trabajo
Route::get('/visit-report/{workReport}/pdf', [VisitReportPdfController::class, 'generateReport'])
    ->name('visit-report.pdf')
    ->middleware('auth');
Route::get(
    '/work-reports/download-multiple/{projectId}',
    [WorkReportExcelController::class, 'downloadMultiplePdf']
)
    ->name('work-reports.download-multiple-pdf')
    ->middleware('auth');
// Rutas para reporte consolidado de trabajo por proyecto
Route::prefix('project/{project}')->middleware('auth')->group(function () {
    Route::get('/consolidated-report/pdf', [WorkReportConsolidatedController::class, 'generateConsolidatedReport'])
        ->name('project.consolidated-report.pdf');

    Route::get('/consolidated-report/preview', action: [WorkReportConsolidatedController::class, 'previewConsolidatedReport'])
        ->name('project.consolidated-report.preview');

    Route::get('/consolidated-report/statistics', [WorkReportConsolidatedController::class, 'getConsolidatedStatistics'])
        ->name('project.consolidated-report.statistics');
});

// Rutas para reporte consolidado de visitas por request
Route::prefix('request/{request}')->middleware('auth')->group(function () {
    Route::get('/consolidated-report/pdf', [RequestConsolidatedController::class, 'generateConsolidatedReport'])
        ->name('request.consolidated-report.pdf');

    Route::get('/consolidated-report/preview', [RequestConsolidatedController::class, 'previewConsolidatedReport'])
        ->name('request.consolidated-report.preview');

    Route::get('/consolidated-report/statistics', [RequestConsolidatedController::class, 'getConsolidatedStatistics'])
        ->name('request.consolidated-report.statistics');
});

// Las rutas de Livewire y Filament se configuran automáticamente
// a través del DashboardPanelProvider

Route::get('/work-report/{workReport}/word', [WorkReportWordController::class, 'generateReport'])
    ->name('work-report.word')
    ->middleware('auth');

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/superfood/public/livewire/livewire.js', $handle);
});

// Ruta pública para estadísticas de cotizaciones
Route::get('/quotes/stats', [QuoteController::class, 'getStatistics']);
Route::get('/quotes/categories', [QuoteController::class, 'categories']);

// Rutas de cotizaciones (requieren autenticación)
Route::prefix('quotes')->middleware('auth')->group(function () {
    Route::get('/', [QuoteController::class, 'index']); // Listar cotizaciones
    Route::post('/', [QuoteController::class, 'store']); // Crear cotización
    Route::get('/{quote}', [QuoteController::class, 'show']); // Ver cotización (API)
    Route::put('/{quote}', [QuoteController::class, 'update']); // Actualizar cotización
    Route::delete('/{quote}', [QuoteController::class, 'destroy']); // Eliminar cotización
    Route::get('/{quote}/preview', [QuoteController::class, 'preview'])->name('quotes.preview');
    Route::get('/{quote}/pdf', [QuoteExportController::class, 'exportPdf'])->name('quotes.pdf');
    Route::get('/{quote}/excel', [QuoteExportController::class, 'exportExcel'])->name('quotes.excel');
});

// Rutas de almacen de cotizaciones (requieren autenticación)
Route::prefix('quoteswarehouse')->middleware('auth')->group(function () {
    Route::get('preview/{quoteWarehouse}', [QuoteWarehouseController::class, 'preview'])->name('quoteswarehouse.preview');
    Route::post('store', [QuoteWarehouseController::class, 'store'])->name('quoteswarehouse.store');
    // Ruta para generar el PDF de atención de suministros
    Route::get('pdf/{quoteWarehouse}', [QuoteWarehouseController::class, 'generatePdf'])
        ->name('quoteswarehouse.pdf')
        ->middleware('auth');
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/superfood/public/livewire/update', $handle);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
});


Route::middleware(['auth'])->group(function () {
    // Actas de Conformidad
    Route::get('/actas/{id}/excel', [ExcelExportController::class, 'downloadActaExcel'])
        ->name('actas.excel');
    Route::get('/actas/{id}/pdf', [ExcelExportController::class, 'downloadActaPdf'])
        ->name('actas.pdf');
    Route::get('/actas/{id}/preview', [ExcelExportController::class, 'previewActaPdf'])
        ->name('actas.preview');
    Route::get('/actas/{id}/pdf-with-reports', [ExcelExportController::class, 'downloadActaWithReports'])
        ->name('actas.pdf-with-reports');

    //DESCARGAR ACTA O REPORTES DE TRABAJO O ACTAS Y REPORTE DE TRABAJO SEGUN EL ID DEL PROYECTO
    Route::get('/descargar-acta-o-reportes/{id}', [ExcelExportController::class, 'downloadAutoActaOrReports'])
        ->name('descargar.acta.o.reportes');

    // Reportes de Trabajo
    Route::get('/work-report/{id}/xls', [WorkReportExcelController::class, 'downloadExcel'])
        ->name('work-report.xls');
    Route::get('/work-report/{id}/pdf-excel', [WorkReportExcelController::class, 'downloadPdf'])
        ->name('work-report.pdf-excel');
    Route::get('/work-report/{id}/pdf', [WorkReportExcelController::class, 'downloadBladePdf'])
        ->name('work-report.pdf');
    Route::get('/work-report/{id}/preview', [WorkReportExcelController::class, 'previewBladePdf'])
        ->name('work-report.preview');

    //aca estarán la importacion de proyectos en csv

    // Ruta para actualizar estado de almacén (Kanban)
    Route::post('/warehouse/update-status', [App\Http\Controllers\WarehouseStatusController::class, 'updateStatus'])
        ->name('warehouse.update-status');
});

Route::get('/crear-symlink', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');

    if (file_exists($link)) {
        return '⚠️ Ya existe un enlace o carpeta llamado "storage" en public.';
    }

    if (symlink($target, $link)) {
        return '✅ Enlace simbólico creado correctamente.';
    } else {
        return '❌ No se pudo crear el enlace simbólico.';
    }
});
