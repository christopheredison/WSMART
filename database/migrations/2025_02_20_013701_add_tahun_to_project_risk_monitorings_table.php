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
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->year('tahun')->after('quarter')->default(date('Y'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
