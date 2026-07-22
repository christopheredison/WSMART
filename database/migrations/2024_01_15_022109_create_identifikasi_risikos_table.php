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
        Schema::create('identifikasi_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_type_id');
            $table->integer('unit_id');
            $table->integer('periode_id');
            $table->integer('user_id');
            $table->string('kategori_risiko_id');
            $table->string('jenis_risiko_id');
            $table->string('peristiwa_risiko_id');
            $table->string('target_capaian_kinerja');
            $table->string('rencana_kegiatan');
            $table->text('deskripsi_peristiwa_risiko');
            $table->string('type');
            $table->text('kontrol_eksisting');
            $table->string('penilaian_efektifitas_kontrol');
            $table->date('perkiraan_waktu_terpapar_risiko_mulai');
            $table->date('perkiraan_waktu_terpapar_risiko_akhir');
            $table->string('status_risiko')->default('proses');
            $table->string('status_progress')->default('risk_officer');
            $table->integer('type_risiko')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identifikasi_risikos');
    }
};
