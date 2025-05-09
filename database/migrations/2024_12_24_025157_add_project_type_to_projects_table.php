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
            $table->integer('project_type')->after('project_name')->nullable()->comment('1: Tender, 2: Operasi');
            $table->integer('project_status')->after('project_type')->nullable()->comment('1: On Going, 2: Finish, 3: Cancel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->dropColumn('project_type');
            $table->dropColumn('project_status');
        });
    }
};
