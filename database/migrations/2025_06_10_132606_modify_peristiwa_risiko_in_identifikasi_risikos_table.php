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
            // ubah peristiwa_risiko_id menjadi nullable
            $table->string('peristiwa_risiko_id')->nullable()->change();

            // tambahkan kolom peristiwa_risiko (text) nullable setelah peristiwa_risiko_id
            $table->text('peristiwa_risiko')->nullable()->after('peristiwa_risiko_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            // kembalikan peristiwa_risiko_id menjadi NOT NULL
            $table->string('peristiwa_risiko_id')->nullable(false)->change();

            // hapus kolom peristiwa_risiko
            $table->dropColumn('peristiwa_risiko');
        });
    }
};
