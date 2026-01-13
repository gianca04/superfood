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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('service_code')->nullable();      // Código de Servicio correlativo
            $table->string('request_number')->nullable();    // N° de Solicitud
            $table->text('comment')->nullable();            // Comentario
            $table->renameColumn('start_date', 'requested_at'); // Renombrar columna a Fecha de Solicitud
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project', function (Blueprint $table) {
            //
            $table->dropColumn('service_code');
            $table->dropColumn('request_number');
            $table->dropColumn('comment');
        });
    }
};
