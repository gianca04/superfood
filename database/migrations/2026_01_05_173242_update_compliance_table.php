<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance', function (Blueprint $table) {
            // Eliminar la columna work_report_id
            $table->dropForeign(['work_report_id']);
            $table->dropColumn('work_report_id');
            
            // Agregar la nueva columna project_id
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            
            // Agregar campos de hora
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('compliance', function (Blueprint $table) {
            // Revertir cambios
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            
            // Restaurar work_report_id
            $table->foreignId('work_report_id')->nullable()->constrained('work_reports')->onDelete('cascade');
        });
    }
};