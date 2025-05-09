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
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            //
            $table->integer('eksposur_risiko')->after('level_risiko')->nullable();
            $table->integer('eksposur_risiko_residual')->after('level_risiko_residual')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            //
            $table->dropColumn('eksposur_risiko');
            $table->dropColumn('eksposur_risiko_residual');
        });
    }
};
