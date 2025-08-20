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
        Schema::create('loss_event_aps', function (Blueprint $table) {
            $table->id();
            $table->text('nama_kejadian')->nullable();
            $table->text('identifikasi_kejadian')->nullable();
            $table->unsignedBigInteger('kategori_kejadian_id')->nullable();
            $table->integer('sumber_penyebab_kejadian')->nullable()->comment('1: internal, 2:eksternal');
            $table->integer('kategori_risiko_bumn')->nullable()->comment('1: Financial, 2: Operational, 3 : Public & Legal');
            $table->text('penjelasan_kerugian')->nullable();
            $table->integer('kejadian_berulang')->nullable();
            $table->integer('frekuensi_kejadian')->nullable();
            $table->integer('status_asuransi')->nullable()->comment('1: Ya, 0 : Tidak');
            $table->bigInteger('nilai_premi')->nullable();
            $table->bigInteger('nilai_klaim')->nullable();
            $table->integer('tahun')->nullable();
            $table->integer('periode_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->date('tanggal_kejadian')->nullable();
            $table->string('kategori_risiko_id')->nullable();
            $table->string('jenis_risiko_id')->nullable();
            $table->string('nilai_kerugian_finansial')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_event_aps');
    }
};
