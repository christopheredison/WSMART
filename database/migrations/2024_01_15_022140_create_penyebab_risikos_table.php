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
        Schema::create('penyebab_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id')->nullable();
            $table->string('penyebab_risiko');
            $table->string('key_risk_indicator')->nullable();
            $table->string('satuan_kri')->nullable();
            $table->string('batas_aman')->nullable();
            $table->string('batas_waspada')->nullable();
            $table->string('batas_bahaya')->nullable();
            $table->text('rencana_perlakuan_risiko')->nullable();
            $table->text('output_perlakuan_risiko')->nullable();
            $table->string('biaya_perlakuan_risiko')->nullable();
            $table->text('progress_rencana_perlakuan_risiko_q1')->nullable();
            $table->text('progress_rencana_perlakuan_risiko_q2')->nullable();
            $table->text('progress_rencana_perlakuan_risiko_q3')->nullable();
            $table->text('progress_rencana_perlakuan_risiko_q4')->nullable();
            $table->text('realisasi_biaya_perlakuan_risiko_q1')->nullable();
            $table->text('realisasi_biaya_perlakuan_risiko_q2')->nullable();
            $table->text('realisasi_biaya_perlakuan_risiko_q3')->nullable();
            $table->text('realisasi_biaya_perlakuan_risiko_q4')->nullable();
            $table->string('status_kri')->nullable();
            $table->string('nilai_kri')->nullable();
            $table->string('pic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyebab_risikos');
    }
};
