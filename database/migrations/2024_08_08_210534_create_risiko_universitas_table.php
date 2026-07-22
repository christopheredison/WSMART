<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risiko_universitas', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_id');
            $table->integer('periode_id');
            $table->integer('batch');
            $table->integer('risiko_id');
            $table->integer('kategori_risiko_id');
            $table->integer('jenis_risiko_id');
            $table->integer('peristiwa_risiko_id');
            $table->integer('target_capaian_kinerja');
            $table->string('rencana_kegiatan')->nullable();
            $table->text('deskripsi_rencana_kegiatan')->nullable();
            $table->text('deskripsi_peristiwa_risiko');
            $table->string('type');
            $table->text('kontrol_eksisting');
            $table->string('penilaian_efektifitas_kontrol');
            $table->date('perkiraan_waktu_terpapar_risiko_mulai');
            $table->date('perkiraan_waktu_terpapar_risiko_akhir');
            $table->string('skala_probabilitas_id')->nullable();
            $table->string('area_dampak')->nullable();
            $table->string('kategori_dampak')->nullable();
            $table->text('deskripsi_dampak')->nullable();
            $table->integer('nilai_dampak')->nullable();
            $table->integer('skala_dampak')->nullable();
            $table->string('nilai_probabilitas')->nullable();
            $table->integer('skala_risiko')->nullable();
            $table->string('level_risiko')->nullable();
            $table->integer('user_id');
            $table->integer('status')->default(1)->comment('1: proses, 2: ranking, 3: konfirmasi, 4: revisi');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risiko_universitas');
    }
};
