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
        Schema::create('loss_events', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_kejadian');
            $table->date('rentang_kejadian_awal');
            $table->date('rentang_kejadian_akhir');
            $table->string('kategori_risiko_id');
            $table->string('jenis_risiko_id');
            $table->string('nilai_kerugian_finansial');
            $table->string('nilai_kerugian_non_fungsional');
            $table->text('peristiwa_kerugian');
            $table->string('unit_penanggung_jawab');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_events');
    }
};
