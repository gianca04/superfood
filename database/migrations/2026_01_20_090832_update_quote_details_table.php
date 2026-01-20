<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            // Solo intentamos borrar budget_code si existe
            if (Schema::hasColumn('quote_details', 'budget_code')) {
                $table->dropColumn('budget_code');
            }

            // Solo intentamos crear subtotal si NO existe ya
            if (!Schema::hasColumn('quote_details', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            if (Schema::hasColumn('quote_details', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (!Schema::hasColumn('quote_details', 'budget_code')) {
                $table->string('budget_code')->nullable();
            }
        });
    }
};
