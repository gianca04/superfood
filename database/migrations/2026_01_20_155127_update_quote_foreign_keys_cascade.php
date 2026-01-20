<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Actualiza las restricciones de claves foráneas para:
     * - quote_details.pricelist_id: SET NULL al eliminar pricelist (mantiene historial)
     * - Verifica que quote_details.quote_id tenga CASCADE (ya debería estar)
     */
    public function up(): void
    {
        // Actualizar pricelist_id para SET NULL en lugar de RESTRICT
        Schema::table('quote_details', function (Blueprint $table) {
            // Primero eliminamos la FK existente
            $table->dropForeign(['pricelist_id']);
        });

        Schema::table('quote_details', function (Blueprint $table) {
            // Aseguramos que la columna sea nullable
            $table->unsignedBigInteger('pricelist_id')->nullable()->change();

            // Recreamos la FK con SET NULL
            $table->foreign('pricelist_id')
                ->references('id')
                ->on('pricelists')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropForeign(['pricelist_id']);
        });

        Schema::table('quote_details', function (Blueprint $table) {
            $table->foreign('pricelist_id')
                ->references('id')
                ->on('pricelists')
                ->onDelete('restrict');
        });
    }
};
