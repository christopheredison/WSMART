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
            foreach (['q1','q2','q3','q4'] as $q) {
                // 1. Probabilitas residual (FK jika ada)
                $table->unsignedBigInteger("skala_probabilitas_residual_id_{$q}")
                      ->nullable();
                // 2. Nilai dampak residual
                $table->decimal("nilai_dampak_residual_{$q}", 20, 2)
                      ->nullable();
                // 3. Skala dampak residual
                $table->integer("skala_dampak_residual_{$q}")
                      ->nullable();
                // 4. Nilai probabilitas residual
                $table->string("nilai_probabilitas_residual_{$q}")
                      ->nullable();
                // 5. Skala risiko residual
                $table->integer("skala_risiko_residual_{$q}")
                      ->nullable();
                // 6. Level risiko residual
                $table->string("level_risiko_residual_{$q}")
                      ->nullable();
                // 7. Eksposur risiko residual
                $table->decimal("eksposur_risiko_residual_{$q}", 20, 2)
                      ->nullable();
                // 8. Deskripsi dampak residual (text)
                $table->text("deskripsi_dampak_residual_{$q}")
                      ->nullable();
                // 9. Asumsi perhitungan dampak residual (text)
                $table->text("asumsi_perhitungan_dampak_residual_{$q}")
                      ->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_analyses', function (Blueprint $table) {
            //
            $cols = [];
            foreach (['q1','q2','q3','q4'] as $q) {
                $cols[] = "skala_probabilitas_residual_id_{$q}";
                $cols[] = "nilai_dampak_residual_{$q}";
                $cols[] = "skala_dampak_residual_{$q}";
                $cols[] = "nilai_probabilitas_residual_{$q}";
                $cols[] = "skala_risiko_residual_{$q}";
                $cols[] = "level_risiko_residual_{$q}";
                $cols[] = "eksposur_risiko_residual_{$q}";
                $cols[] = "deskripsi_dampak_residual_{$q}";
                $cols[] = "asumsi_perhitungan_dampak_residual_{$q}";
            }
            $table->dropColumn($cols);
        });
    }
};
