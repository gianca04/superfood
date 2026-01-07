<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, limpiar los datos existentes en personnel, materials y tools
        // Convertir los datos de texto a formato JSON vacío o estructura básica
        DB::table('work_reports')->update([
            'personnel' => json_encode([]),
            'materials' => json_encode([]),
            'tools' => json_encode([])
        ]);

        Schema::table('work_reports', function (Blueprint $table) {
            // Cambiar personnel, materials y tools a tipo JSON
            $table->json('personnel')->nullable()->change();
            $table->json('materials')->nullable()->change();
            $table->json('tools')->nullable()->change();

            // Renombrar la columna 'description' a 'work_to_do'
            $table->renameColumn('description', 'work_to_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_reports', function (Blueprint $table) {
            // Revertir personnel, materials y tools a longText
            $table->longText('personnel')->nullable()->change();
            $table->longText('materials')->nullable()->change();
            $table->longText('tools')->nullable()->change();

            // Revertir el nombre de la columna 'work_to_do' a 'description'
            $table->renameColumn('work_to_do', 'description');
        });
    }
};
