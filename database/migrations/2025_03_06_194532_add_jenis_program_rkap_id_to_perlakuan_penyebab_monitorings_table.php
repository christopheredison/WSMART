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
        Schema::table('perlakuan_penyebab_monitorings', function (Blueprint $table) {
            
            // Menambahkan kolom sebelum 'jenis_program_rkap'
            $table->unsignedBigInteger('jenis_program_rkap_id')->nullable()->after('jenis_program_rkap');

            // Menambahkan foreign key ke tabel 'jenis_program_dalam_rkap'
            $table->foreign('jenis_program_rkap_id')
                ->references('id')
                ->on('jenis_program_dalam_rkap')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perlakuan_penyebab_monitorings', function (Blueprint $table) {
            //
            $table->dropForeign(['jenis_program_rkap_id']);
            $table->dropColumn('jenis_program_rkap_id');
        });
    }

};
