<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDivisiTerkaitToPerlakuanPenyebabRisikosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perlakuan_penyebab_risikos', function (Blueprint $table) {
            $table->json('divisi_terkait')->nullable()->after('pic_jabatan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('perlakuan_penyebab_risikos', function (Blueprint $table) {
            $table->dropColumn('divisi_terkait');
        });
    }
}