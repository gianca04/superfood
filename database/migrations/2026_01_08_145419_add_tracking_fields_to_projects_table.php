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
            //
            // 4. TRACKING DATA
            $table->string('status')->default('pending');
            $table->timestamp('quote_sent_at')->nullable();
            $table->timestamp('quote_approved_at')->nullable();
            $table->timestamp('wo_review_at')->nullable();
            $table->timestamp('wo_completed_at')->nullable();
            $table->integer('days_to_completion')->nullable();
            $table->text('final_comments')->nullable();
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
                'status',
                'quote_sent_at',
                'quote_approved_at',
                'wo_review_at',
                'wo_completed_at',
                'days_to_completion',
                'final_comments',
            ]);
        });
    }
};
