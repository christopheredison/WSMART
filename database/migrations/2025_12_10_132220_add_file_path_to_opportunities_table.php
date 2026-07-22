<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('nilai_peluang_realisasi');
        });
    }

    public function down()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
