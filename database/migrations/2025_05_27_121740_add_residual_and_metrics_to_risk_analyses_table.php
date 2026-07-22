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
        Schema::table('risk_analyses', function (Blueprint $table) {
            //
            // 1. Deskripsi dampak residual
            $table->text('deskripsi_dampak_residual')
                  ->nullable()
                  ->after('deskripsi_dampak');

            // 2. Asumsi perhitungan dampak
            $table->text('asumsi_perhitungan_dampak')
                  ->nullable()
                  ->after('deskripsi_dampak_residual');

            // 3. Asumsi perhitungan dampak residual
            $table->text('asumsi_perhitungan_dampak_residual')
                  ->nullable()
                  ->after('asumsi_perhitungan_dampak');

            // 4. Eksposur risiko
            $table->decimal('eksposur_risiko', 20, 2)
                  ->nullable()
                  ->after('asumsi_perhitungan_dampak_residual');

            // 5. Risk limit
            $table->decimal('risk_limit', 20, 2)
                  ->nullable()
                  ->after('eksposur_risiko');

            // 6. Asumsi nilai dampak
            $table->decimal('asumsi_nilai_dampak', 20, 2)
                  ->nullable()
                  ->after('risk_limit');

            // 7. Skala probabilitas residual (FK ke skala_probabilitas)
            $table->unsignedBigInteger('skala_probabilitas_residual_id')
                  ->nullable()
                  ->after('asumsi_nilai_dampak');

            // 8. Nilai dampak residual
            $table->decimal('nilai_dampak_residual', 20, 2)
                  ->nullable()
                  ->after('skala_probabilitas_residual_id');

            // 9. Skala dampak residual
            $table->integer('skala_dampak_residual')
                  ->nullable()
                  ->after('nilai_dampak_residual');

            // 10. Nilai probabilitas residual
            $table->string('nilai_probabilitas_residual', 255)
                  ->nullable()
                  ->after('skala_dampak_residual');

            // 11. Skala risiko residual
            $table->integer('skala_risiko_residual')
                  ->nullable()
                  ->after('nilai_probabilitas_residual');

            // 12. Level risiko residual
            $table->string('level_risiko_residual', 255)
                  ->nullable()
                  ->after('skala_risiko_residual');

            // 13. Eksposur risiko residual
            $table->decimal('eksposur_risiko_residual', 20, 2)
                  ->nullable()
                  ->after('level_risiko_residual');

            // Foreign key constraint
            $table->foreign('skala_probabilitas_residual_id')
                  ->references('id')
                  ->on('skala_probabilitas')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_analyses', function (Blueprint $table) {
            //
            // Hapus FK lalu kolomnya
            $table->dropForeign(['skala_probabilitas_residual_id']);

            $table->dropColumn([
                'deskripsi_dampak_residual',
                'asumsi_perhitungan_dampak',
                'asumsi_perhitungan_dampak_residual',
                'eksposur_risiko',
                'risk_limit',
                'asumsi_nilai_dampak',
                'skala_probabilitas_residual_id',
                'nilai_dampak_residual',
                'skala_dampak_residual',
                'nilai_probabilitas_residual',
                'skala_risiko_residual',
                'level_risiko_residual',
                'eksposur_risiko_residual',
            ]);
        });
    }
};
