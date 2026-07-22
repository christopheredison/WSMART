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
        Schema::create('rencana_perlakuan_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');
            $table->string('opsi_perlakuan_risiko')->nullable();
            $table->string('target_nilai_dampak_q1')->nullable();
            $table->string('target_skala_dampak_q1')->nullable();
            $table->string('target_nilai_probabilitas_q1')->nullable();
            $table->string('target_skala_probabilitas_q1')->nullable();
            $table->string('target_skala_risiko_q1')->nullable();
            $table->string('target_level_risiko_q1')->nullable();
            $table->string('target_nilai_dampak_q2')->nullable();
            $table->string('target_skala_dampak_q2')->nullable();
            $table->string('target_nilai_probabilitas_q2')->nullable();
            $table->string('target_skala_probabilitas_q2')->nullable();
            $table->string('target_skala_risiko_q2')->nullable();
            $table->string('target_level_risiko_q2')->nullable();
            $table->string('target_nilai_dampak_q3')->nullable();
            $table->string('target_skala_dampak_q3')->nullable();
            $table->string('target_nilai_probabilitas_q3')->nullable();
            $table->string('target_skala_probabilitas_q3')->nullable();
            $table->string('target_skala_risiko_q3')->nullable();
            $table->string('target_level_risiko_q3')->nullable();
            $table->string('target_nilai_dampak_q4')->nullable();
            $table->string('target_skala_dampak_q4')->nullable();
            $table->string('target_nilai_probabilitas_q4')->nullable();
            $table->string('target_skala_probabilitas_q4')->nullable();
            $table->string('target_skala_risiko_q4')->nullable();
            $table->string('target_level_risiko_q4')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_perlakuan_risikos');
    }
};
