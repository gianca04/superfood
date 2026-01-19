<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupervisorNameToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('supervisor_name')->nullable()->after('final_comments');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('supervisor_name');
        });
    }
}
