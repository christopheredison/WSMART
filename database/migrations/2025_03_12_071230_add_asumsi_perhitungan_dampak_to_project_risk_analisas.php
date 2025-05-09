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
            $table->text('asumsi_perhitungan_dampak')->nullable()->after('deskripsi_dampak');
            $table->text('asumsi_perhitungan_dampak_residual')->nullable()->after('asumsi_perhitungan_dampak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            //
            $table->dropColumn(['asumsi_perhitungan_dampak', 'asumsi_perhitungan_dampak_residual']);
        });
    }
};
