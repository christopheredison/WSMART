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
            // Tambah kolom foreign key, nullable, setelah kolom 'kontrol_eksisting'
            $table->unsignedBigInteger('jenis_kontrol_eksisting_id')
                  ->nullable()
                  ->after('kontrol_eksisting');

            // Definisikan relasi ke tabel jenis_kontrol_eksistings
            $table->foreign('jenis_kontrol_eksisting_id')
                  ->references('id')
                  ->on('jenis_kontrol_eksistings')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            // Hapus constraint dulu baru kolomnya
            $table->dropForeign(['jenis_kontrol_eksisting_id']);
            $table->dropColumn('jenis_kontrol_eksisting_id');
        });
    }
};
