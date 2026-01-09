<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('fracttal_status')->nullable();
            $table->string('purchase_order')->nullable()->after('fracttal_status');
            $table->string('migo_code')->nullable()->after('purchase_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Revirtiendo los cambios en caso de rollback
            $table->dropColumn('fracttal_status');
            $table->dropColumn('purchase_order');
            $table->dropColumn('migo_code');
        });
    }
};
