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
        Schema::table('penilaian_capaian_kinerjas', function (Blueprint $table) {
            //
            // Total nilai capaian kinerja (0–100)
            $table->decimal('total_nilai_capaian_kinerja', 5, 2)
                  ->nullable()->default(0)->after('rmi_period_id');

            // Skala (relasi ke tabel skala_kinerjas)
            $table->foreignId('capaian_kinerja')
                  ->nullable()
                  ->after('total_nilai_capaian_kinerja')
                  ->constrained('skala_kinerjas')
                  ->nullOnDelete();

            // Total nilai KPMR (0–100)
            $table->decimal('total_nilai_kpmr', 5, 2)
                  ->nullable()->default(0)->after('capaian_kinerja');

            // Skala KPMR (relasi ke tabel skala_kpmrs)
            $table->foreignId('kpmr')
                  ->nullable()
                  ->after('total_nilai_kpmr')
                  ->constrained('skala_kpmrs')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian_capaian_kinerjas', function (Blueprint $table) {
            //
            $table->dropForeign(['kpmr']);
            $table->dropForeign(['capaian_kinerja']);
            $table->dropColumn(['total_nilai_capaian_kinerja','capaian_kinerja','total_nilai_kpmr','kpmr']);
        });
    }
};
