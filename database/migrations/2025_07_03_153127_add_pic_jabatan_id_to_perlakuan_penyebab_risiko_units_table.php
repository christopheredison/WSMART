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
        Schema::table('perlakuan_penyebab_risiko_units', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('pic_jabatan_id')->nullable()->after('pic');
            $table->foreign('pic_jabatan_id')->references('id')->on('jabatans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perlakuan_penyebab_risiko_units', function (Blueprint $table) {
            //
            $table->dropForeign(['pic_jabatan_id']);
            $table->dropColumn('pic_jabatan_id');
        });
    }
};
