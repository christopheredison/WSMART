<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->integer('sikap_risiko')->after('deskripsi')->comment('1: Konservatif, 2: Moderat, 3: Agresif')->nullable();
        });
    }

    public function down()
    {
        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->dropColumn('sikap_risiko');
        });
    }
};
