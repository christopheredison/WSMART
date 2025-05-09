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
            $table->text('deskripsi_dampak_residual')->nullable()->after('deskripsi_dampak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            //
            $table->dropColumn('deskripsi_dampak_residual');
        });
    }
};
