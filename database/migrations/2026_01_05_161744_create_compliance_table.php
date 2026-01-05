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
        Schema::create('compliance', function (Blueprint $table) {
            $table->id();
            // Relación
            $table->foreignId('work_report_id')
                ->constrained('work_reports')
                ->cascadeOnDelete();
             // Sección B1
            $table->json(column: 'assets')->nullable();

            // Sección B2
            $table->text('maintenance_observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance');
    }
};
