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
        Schema::table('prioritas_risikos', function (Blueprint $table) {
            //
            Schema::table('prioritas_risikos', function (Blueprint $table) {
                // Mengubah kolom dari string ke integer
                $table->integer('kategori_risiko_id')->change();
                $table->integer('jenis_risiko_id')->change();
                $table->integer('peristiwa_risiko_id')->change();
                $table->integer('target_capaian_kinerja')->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prioritas_risikos', function (Blueprint $table) {
            //
        });
    }
};
