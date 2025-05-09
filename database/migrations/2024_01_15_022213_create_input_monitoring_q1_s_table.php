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
        Schema::create('input_monitoring_q1_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rencana_perlakuan_risiko_id');
            $table->foreign('rencana_perlakuan_risiko_id')->references('id')->on('rencana_perlakuan_risikos');
            $table->text('deskripsi_peristiwa_risiko');
            $table->string('nilai_dampak_inherent');
            $table->string('skala_dampak_inherent');
            $table->string('nilai_probabilitas_inherent');
            $table->string('skala_probabilitas_inherent');
            $table->string('skala_risiko_inherent');
            $table->string('level_risiko_inherent');
            $table->string('realisasi_nilai_dampak_q1');
            $table->string('target_skala_dampak_q1');
            $table->string('realisasi_nilai_probabilitas_q1');
            $table->string('realisasi_skala_probabilitas_q1');
            $table->string('realisasi_skala_risiko_q1');
            $table->string('realisasi_level_risiko_q1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_monitoring_q1_s');
    }
};
