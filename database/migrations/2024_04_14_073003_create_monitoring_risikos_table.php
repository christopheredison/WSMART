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
        Schema::create('monitoring_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');
            $table->integer('rencana_perlakuan_risiko_id');
            $table->string('periode_monitoring');
            $table->string('nilai_dampak_inherent')->nullable();
            $table->string('skala_dampak_inherent')->nullable();
            $table->string('nilai_probabilitas_inherent')->nullable();
            $table->string('skala_probabilitas_inherent')->nullable();
            $table->string('skala_risiko_inherent')->nullable();
            $table->string('level_risiko_inherent')->nullable();
            $table->string('target_nilai_dampak')->nullable();
            $table->string('target_skala_dampak')->nullable();
            $table->string('target_nilai_probabilitas')->nullable();
            $table->string('target_skala_probabilitas')->nullable();
            $table->string('target_skala_risiko')->nullable();
            $table->string('target_level_risiko')->nullable();
            $table->string('realisasi_nilai_dampak')->nullable();
            $table->string('realisasi_skala_dampak')->nullable();
            $table->string('realisasi_nilai_probabilitas')->nullable();
            $table->string('realisasi_skala_probabilitas')->nullable();
            $table->string('realisasi_skala_risiko')->nullable();
            $table->string('realisasi_level_risiko')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_risikos');
    }
};
