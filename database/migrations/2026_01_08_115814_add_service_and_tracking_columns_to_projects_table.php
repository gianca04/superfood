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
            // 2. SERVICE DATA (EXECUTION)
            $table->string('work_order_number')->nullable()->after('comment')->comment('OT / Orden de Trabajo');
            $table->date('service_start_date')->nullable();
            $table->date('service_end_date')->nullable();
            $table->integer('service_days')->nullable()->comment('Días calculados');
            $table->string('task_type')->nullable()->comment('OPEX / CAPEX');

            // Document Flags (SI/NO)
            $table->string('has_quote')->default('NO');        // cotizacion
            $table->string('has_report')->default('NO');       // informe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->dropColumn([
                'work_order_number',
                'service_start_date',
                'service_end_date',
                'service_days',
                'task_type',
                'has_quote',
                'has_report',
            ]);
        });
    }
};
