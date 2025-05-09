<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPrioritasRisikosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prioritas_risikos', function (Blueprint $table) {
            // Mengubah kolom 'rencana_kegiatan' agar nullable
            $table->string('rencana_kegiatan')->nullable()->change();
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
            // Mengembalikan kolom 'rencana_kegiatan' ke keadaan semula (tidak nullable)
            //$table->string('rencana_kegiatan')->nullable(false)->change();
        });
    }
}
