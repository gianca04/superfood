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
        Schema::create('quote_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')
                ->constrained('quotes')
                ->cascadeOnDelete();
            $table->integer('line')->default(1);
            $table->string('budget_code')->nullable();
            $table->enum('item_type', ['SERVICIO', 'VIATICOS', 'SUMINISTRO', 'MANO DE OBRA', 'OTROS'])
                ->default('SERVICIO');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index(['quote_id', 'line']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_details');
    }
};
