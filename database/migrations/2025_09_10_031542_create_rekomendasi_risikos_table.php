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
        Schema::create('rekomendasi_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_type_id');
            $table->integer('unit_id');
            $table->integer('periode_id');
            $table->integer('user_id');
            $table->string('kategori_risiko_id');
            $table->string('jenis_risiko_id');
            $table->unsignedBigInteger('peristiwa_risiko_id')->nullable();
            $table->text('peristiwa_risiko')->nullable();
            $table->text('wbs')->nullable();
            $table->unsignedBigInteger('tck_id')->nullable();
            $table->text('target_capaian_kinerja')->nullable();
            $table->string('rencana_kegiatan')->nullable();
            $table->text('deskripsi_peristiwa_risiko')->nullable();
            $table->text('deskripsi_rencana_kegiatan')->nullable();
            $table->text('type')->nullable();
            $table->text('kontrol_eksisting')->nullable();
            $table->unsignedBigInteger('jenis_kontrol_eksisting_id')->nullable();
            $table->text('penilaian_efektifitas_kontrol')->nullable();
            $table->date('perkiraan_waktu_terpapar_risiko_mulai');
            $table->date('perkiraan_waktu_terpapar_risiko_akhir');
            $table->text('status_risiko')->nullable();
            $table->tinyInteger('previous_status_risiko')->unsigned()->nullable();
            $table->text('status_progress')->nullable();
            $table->integer('type_risiko')->nullable();
            $table->text('catatan')->nullable();
            $table->integer('skala_risiko')->nullable();
            $table->string('level_risiko')->nullable();
            $table->integer('status')->default(1)->comment('1: Draft, 2: Published');
            $table->integer('step_verification')->nullable();
            $table->integer('is_corporate')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->decimal('efektivitas_perlakuan_risiko', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_risikos');
    }
};
