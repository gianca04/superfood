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
        Schema::create('quote_warehouse_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_warehouse_id')->constrained('quote_warehouse')->onDelete('cascade');
            $table->foreignId('quote_detail_id')->constrained('quote_details')->onDelete('cascade');
            $table->decimal('attended_quantity', 10, 2); // cantidad_atendida
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_warehouse_details');
    }
};
