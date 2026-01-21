<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatusEnumInQuotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Cambiar el enum a los nuevos valores
            $table->enum('status', ['Pendiente', 'Enviado', 'Aprobado', 'Anulado'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Revertir al enum original (basado en el modelo Quote.php)
            $table->enum('status', ['POR HACER', 'ENVIADO', 'APROBADO', 'RECHAZADA'])->change();
        });
    }
}
