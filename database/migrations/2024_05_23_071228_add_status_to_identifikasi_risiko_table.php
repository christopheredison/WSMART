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
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
            $table->integer('skala_risiko')->after('catatan')->nullable();
            $table->string('level_risiko')->after('skala_risiko')->nullable();
            $table->integer('status')->after('level_risiko')->comment('1: Input Data, 2: Dikirim, 3: Tunggu Verifikasi , 4: Terverifikasi')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
            $table->dropColumn('skala_risiko');
            $table->dropColumn('level_risiko');
            $table->dropColumn('status');
        });
    }
};
