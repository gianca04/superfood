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
        Schema::table('compliance', function (Blueprint $table) {
            // Datos del cliente
            $table->string('fullname_cliente')->nullable();
            $table->string('document_type')->nullable(); // DNI, CARNET DE EXTRANJERIA, PASAPORTE
            $table->string('document_number')->nullable();
            $table->text('client_signature')->nullable(); // Firma del cliente (base64)
            
            // Firma del empleado
            $table->text('employee_signature')->nullable(); // Firma del empleado (base64)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compliance', function (Blueprint $table) {
            $table->dropColumn([
                'fullname_cliente',
                'document_type',
                'document_number',
                'client_signature',
                'employee_signature',
            ]);
        });
    }
};
