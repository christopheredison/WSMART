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
            Schema::table('project_risk_analisas', function (Blueprint $table) {
                $table->decimal('asumsi_nilai_dampak', 15, 2)
                      ->after('risk_limit')
                      ->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            $table->dropColumn('asumsi_nilai_dampak');
        });
    }
};
