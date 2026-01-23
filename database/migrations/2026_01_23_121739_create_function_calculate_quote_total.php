<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            DROP FUNCTION IF EXISTS calculate_quote_total;
            CREATE FUNCTION calculate_quote_total(quoteId INT) RETURNS DECIMAL(10,2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE total DECIMAL(10,2);
                SELECT IFNULL(SUM(ROUND(quantity * unit_price, 2)), 0) INTO total
                FROM quote_details
                WHERE quote_id = quoteId;
                RETURN total;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_quote_total');
    }
};
