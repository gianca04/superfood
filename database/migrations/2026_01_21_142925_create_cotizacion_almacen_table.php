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
        Schema::create('quote_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status')->default('pending'); // atendido, parcial, pendiente -> attended, partial, pending
            $table->timestamp('attended_at')->nullable(); // fecha_atencion
            $table->text('observations')->nullable(); // observaciones
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_warehouse');
    }
};
