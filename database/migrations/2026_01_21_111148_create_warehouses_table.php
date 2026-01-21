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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id(); // Identificador único (BigInt)
            $table->string('name'); // Nombre descriptivo
            $table->text('location')->nullable(); // Dirección física o referencia
            $table->foreignId('manager_id')->constrained('employees'); // Gestor (Foreign Key -> employees)
            $table->boolean('is_active')->default(true); // Estado del almacén
            $table->timestamps(); // Fecha de registro y última modificación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
