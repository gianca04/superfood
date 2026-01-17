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
        Schema::table('pricelists', function (Blueprint $table) {
            $table->foreignId('price_type_id')->nullable()->after('unit_id')->constrained('price_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricelists', function (Blueprint $table) {
            $table->dropForeign(['price_type_id']);
            $table->dropColumn('price_type_id');
        });
    }
};
