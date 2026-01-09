<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_reports', function (Blueprint $table) {
            $table->foreignId('compliance_id')
                ->nullable()
                ->after('project_id') // Lo ubicamos después de project_id por orden lógico
                ->constrained('compliance')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_reports', function (Blueprint $table) {
            Schema::table('work_reports', function (Blueprint $table) {
                // Eliminamos la clave foránea y luego la columna
                $table->dropForeign(['compliance_id']);
                $table->dropColumn('compliance_id');
            });
        });
    }
};
