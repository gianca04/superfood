<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // 1. Relación con el Proyecto
            $table->foreignId('project_id')->after('id')->nullable()->constrained('projects')->onDelete('cascade');

            // 2. Renombrar campos existentes para que coincidan con el formulario
            $table->renameColumn('employee_id', 'inspector_id'); // Inspector
            $table->renameColumn('report_date', 'visit_date');  // Fecha visita
            $table->renameColumn('start_time', 'entry_time');  // Hora ingreso
            $table->renameColumn('end_time', 'exit_time');    // Hora salida

            // 3. Añadir campos nuevos que no existían
            $table->foreignId('quoted_by_id')->nullable()->after('inspector_id')->constrained('employees'); // Cotizador
            $table->decimal('amount', 10, 2)->nullable()->after('exit_time'); // Monto SOL

            // Nota: 'description' se queda igual para "Comentario2"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            //
            $table->dropForeign(['project_id']);
            $table->dropForeign(['quoted_by_id']);

            // Revertir nombres
            $table->renameColumn('inspector_id', 'employee_id');
            $table->renameColumn('visit_date', 'report_date');
            $table->renameColumn('entry_time', 'start_time');
            $table->renameColumn('exit_time', 'end_time');

            $table->dropColumn(['project_id', 'quoted_by_id', 'amount']);
        });
    }
};
