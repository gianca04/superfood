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
        Schema::create('project_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // Vital key
            $table->foreignId('quote_warehouse_detail_id')->constrained('quote_warehouse_details')->onDelete('cascade'); // Traceability
            $table->foreignId('work_report_id')->nullable()->constrained('work_reports')->onDelete('set null'); // Optional tracking (renamed from daily_report_id)
            $table->decimal('quantity', 10, 2); // Quantity installed/spent
            $table->date('consumed_at'); // Consumption date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_consumptions');
    }
};
