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
            $table->decimal('aktual_current', 20, 2)->default(0)->after('eksposure_risiko');
            $table->decimal('aktual_month_1', 20, 2)->default(0)->after('aktual_current');
            $table->decimal('aktual_month_2', 20, 2)->default(0)->after('aktual_month_1');
            $table->string('aktual_status')->nullable()->after('aktual_month_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->dropColumn(['aktual_current', 'aktual_month_1', 'aktual_month_2', 'aktual_status']);
        });
    }
};
