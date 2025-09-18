<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            // 1: Draft/Revisi, 2: Menunggu Verifikasi, 3: Terverifikasi
            $table->tinyInteger('status')->default(1)->after('month')->comment('1: Draft/Revisi, 2: Menunggu Verifikasi, 3. Terverifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};