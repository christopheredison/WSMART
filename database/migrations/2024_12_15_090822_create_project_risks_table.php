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
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_type_id');//berelasi ke model UnitType (table unit_types)
            $table->integer('unit_id'); //berelasi ke model Unit (table units)
            $table->integer('periode_id'); //berelasi ke model Periode (table periodes)
            $table->integer('user_id'); //berelasi ke model User (table users)
            $table->integer('project_id'); //berelasi ke model Project (table projects)
            $table->string('kategori_risiko_id'); //berelasi ke model KategoriRisiko (table kategori_risikos)
            $table->string('jenis_risiko_id'); //berelasi ke model JenisRisiko (table jenis_risikos)
            $table->integer('peristiwa_risiko_id'); //berelasi ke model PeristiwaRisiko (table peristiwa_risikos)
            $table->integer('target_capaian_kinerja')->nullable(); //berelasi ke model Tck (table tcks)
            $table->string('rencana_kegiatan')->nullable();
            $table->text('deskripsi_peristiwa_risiko');
            $table->string('type')->default('Umum');
            $table->integer('jenis_kontrol_eksisting_id');//berelasi ke model JenisKontrolEksisting (table jenis_kontrol_eksistings)
            $table->integer('kontrol_eksisting'); //berelasi ke model KontrolEksisting (table kontrol_eksistings)
            $table->integer('penilaian_efektifitas_kontrol'); //berelasi ke model PenilaianEfektivitasKontrol (table penilaian_efektivitas_kontrols)
            $table->date('perkiraan_waktu_terpapar_risiko_mulai');
            $table->date('perkiraan_waktu_terpapar_risiko_akhir');
            $table->string('status_risiko')->default('proses');
            $table->string('status_progress')->default('risk_officer');
            $table->integer('type_risiko')->default(0);
            $table->text('catatan')->nullable();
            $table->integer('skala_risiko')->nullable();
            $table->string('level_risiko')->nullable();
            $table->integer('status')->comment('1: Input Data, 2: Dikirim, 3: Tunggu Verifikasi , 4: Terverifikasi')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};
