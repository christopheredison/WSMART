<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeskripsiRencanaKegiatanToPrioritasRisikosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prioritas_risikos', function (Blueprint $table) {
            // Menambahkan kolom 'deskripsi_rencana_kegiatan' setelah kolom 'rencana_kegiatan'
            $table->text('deskripsi_rencana_kegiatan')->nullable()->after('rencana_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prioritas_risikos', function (Blueprint $table) {
            // Menghapus kolom 'deskripsi_rencana_kegiatan'
            $table->dropColumn('deskripsi_rencana_kegiatan');
        });
    }
}
