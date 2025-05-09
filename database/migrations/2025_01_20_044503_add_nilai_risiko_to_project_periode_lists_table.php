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
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->integer('skala_risiko')->nullable()->after('unit_id');
            $table->string('level_risiko')->nullable()->after('skala_risiko');
            $table->integer('skala_risiko_residual')->nullable()->after('level_risiko');
            $table->string('level_risiko_residual')->nullable()->after('skala_risiko_residual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->dropColumn('skala_risiko');
            $table->dropColumn('level_risiko');
            $table->dropColumn('skala_risiko_residual');
            $table->dropColumn('level_risiko_residual');
        });
    }
};
