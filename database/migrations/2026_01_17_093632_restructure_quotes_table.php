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
        Schema::table('quotes', function (Blueprint $table) {
            // Eliminar foreign keys antiguas
            $table->dropForeign(['client_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['sub_client_id']);
            $table->dropForeign(['quote_category_id']);

            // Eliminar columnas antiguas
            $table->dropColumn([
                'client_id',
                'employee_id',
                'sub_client_id',
                'quote_category_id',
                'TDR',
                'quote_file',
                'correlative',
                'contractor',
                'pe_pt',
                'project_description',
                'location',
                'delivery_term',
                'status',
                'comment',
            ]);
        });

        Schema::table('quotes', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->string('request_number')->nullable()->after('id');
            $table->foreignId('employee_id')
                ->nullable()
                ->after('request_number')
                ->constrained('employees')
                ->nullOnDelete();
            $table->foreignId('sub_client_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('sub_clients')
                ->nullOnDelete();
            $table->foreignId('quote_category_id')
                ->nullable()
                ->after('sub_client_id')
                ->constrained('quote_categories')
                ->nullOnDelete();
            $table->string('energy_sci_manager')->nullable()->after('quote_category_id');
            $table->string('ceco')->nullable()->after('energy_sci_manager');
            $table->enum('status', ['POR HACER', 'ENVIADO', 'APROBADO', 'RECHAZADA'])
                ->default('POR HACER')
                ->after('ceco');
            $table->date('quote_date')->nullable()->after('status');
            $table->date('execution_date')->nullable()->after('quote_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Eliminar nuevas foreign keys
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['sub_client_id']);
            $table->dropForeign(['quote_category_id']);

            // Eliminar nuevas columnas
            $table->dropColumn([
                'request_number',
                'employee_id',
                'sub_client_id',
                'quote_category_id',
                'energy_sci_manager',
                'ceco',
                'status',
                'quote_date',
                'execution_date',
            ]);
        });

        Schema::table('quotes', function (Blueprint $table) {
            // Restaurar columnas antiguas
            $table->unsignedBigInteger('client_id')->after('id');
            $table->unsignedBigInteger('employee_id')->after('client_id');
            $table->unsignedBigInteger('sub_client_id')->after('employee_id');
            $table->foreignId('quote_category_id')->nullable()->after('sub_client_id');
            $table->string('TDR')->after('quote_category_id');
            $table->string('quote_file')->nullable()->after('TDR');
            $table->string('correlative')->unique()->after('quote_file');
            $table->string('contractor')->after('correlative');
            $table->enum('pe_pt', ['PT', 'PE', 'PE_PT'])->after('contractor');
            $table->string('project_description')->after('pe_pt');
            $table->string('location')->after('project_description');
            $table->date('delivery_term')->after('location');
            $table->enum('status', [
                'unassigned',
                'in_progress',
                'under_review',
                'sent',
                'rejected',
                'accepted'
            ])->after('delivery_term');
            $table->text('comment')->nullable()->after('status');

            // Restaurar foreign keys
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('sub_client_id')->references('id')->on('sub_clients')->onDelete('cascade');
            $table->foreign('quote_category_id')->references('id')->on('quote_categories')->nullOnDelete();
        });
    }
};
