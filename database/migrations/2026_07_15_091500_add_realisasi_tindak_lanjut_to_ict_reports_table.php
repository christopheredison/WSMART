<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_reports', function (Blueprint $table) {
            $table->text('realisasi_tindak_lanjut')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('ict_reports', function (Blueprint $table) {
            $table->dropColumn('realisasi_tindak_lanjut');
        });
    }
};
