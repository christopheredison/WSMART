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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->after('message')->nullable()->comment('Jenis notifikasi: risiko, kri, laporan, mitigasi, evaluasi');
            $table->string('severity')->after('type')->nullable()->comment('Tingkat keparahan: high, medium, low');
            $table->string('color')->after('severity')->nullable()->comment('Warna notifikasi: danger, warning, info, success, primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'severity', 'color']);
        });
    }
};