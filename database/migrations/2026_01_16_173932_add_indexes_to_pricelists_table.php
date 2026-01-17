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
            $table->index('sat_line', 'pricelists_sat_line_index');
            $table->fullText('sat_description', 'pricelists_sat_description_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricelists', function (Blueprint $table) {
            $table->dropIndex('pricelists_sat_line_index');
            $table->dropFullText('pricelists_sat_description_fulltext');
        });
    }
};
