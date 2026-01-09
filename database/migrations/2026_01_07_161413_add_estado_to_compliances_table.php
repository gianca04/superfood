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
        Schema::table('compliance', function (Blueprint $table) {
            // Usamos string o enum. 'pendiente' como default es lo común.
            $table->string('state')->default('pendiente')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('compliance', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
