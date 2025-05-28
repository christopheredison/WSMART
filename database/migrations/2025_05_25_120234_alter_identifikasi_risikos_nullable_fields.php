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
            $table->text('target_capaian_kinerja')
                  ->nullable()
                  ->change();

            $table->text('deskripsi_peristiwa_risiko')
                  ->nullable()
                  ->change();

            $table->text('type')
                  ->nullable()
                  ->change();

            $table->text('kontrol_eksisting')
                  ->nullable()
                  ->change();

            $table->text('penilaian_efektifitas_kontrol')
                  ->nullable()
                  ->change();
            
            $table->text('status_risiko')
                  ->nullable()
                  ->change();
                  
            $table->text('status_progress')
                  ->nullable()
                  ->change();

            $table->integer('type_risiko')
                  ->nullable()
                  ->change();

            $table->integer('status')
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
        });
    }
};
