<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            if (Schema::hasColumn('quote_details', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('quote_details', 'line')) {
                $table->renameColumn('line', 'pricelist_id');
            }
        });

        Schema::table('quote_details', function (Blueprint $table) {
            $table->unsignedBigInteger('pricelist_id')->nullable()->change();

            $table->foreign('pricelist_id')
                ->references('id')
                ->on('pricelists')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropForeign(['pricelist_id']);
            $table->renameColumn('pricelist_id', 'line');
            $table->text('description')->nullable();
        });
    }
};
