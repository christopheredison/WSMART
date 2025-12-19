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
        Schema::table('rmi_periods', function (Blueprint $table) {
            // Metadata Penilai Eksternal
            $table->string('penilai_external')
                  ->nullable()
                  ->after('penilaian')
                  ->comment('Nama Instansi/Lembaga Penilai Eksternal');

            // Score RMI Eksternal (ditaruh setelah score internal)
            $table->decimal('score_rmi_external', 8, 2)
                  ->nullable()
                  ->after('score_rmi_desc');
            
            $table->string('score_rmi_external_desc')
                  ->nullable()
                  ->after('score_rmi_external');

            // Aspek Kinerja & KPMR Eksternal
            $table->string('kinerja_external')
                  ->nullable()
                  ->after('kinerja');
                  
            $table->string('kpmr_external')
                  ->nullable()
                  ->after('kpmr');

            // Hasil Akhir Eksternal
            $table->integer('peringkat_komposit_risiko_external')
                  ->nullable()
                  ->after('peringkat_komposit_risiko');

            $table->decimal('nilai_konversi_external', 8, 2)
                  ->nullable()
                  ->after('nilai_konversi');

            $table->decimal('score_aspek_kinerja_external', 10, 2)
                  ->nullable()
                  ->after('score_aspek_kinerja');

            $table->decimal('adjusment_score_external', 10, 2)
                  ->nullable()
                  ->after('adjusment_score');

            $table->decimal('final_score_rmi_external', 10, 2)
                  ->nullable()
                  ->after('final_score_rmi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            $table->dropColumn([
                'penilai_external',
                'score_rmi_external',
                'score_rmi_external_desc',
                'kinerja_external',
                'kpmr_external',
                'peringkat_komposit_risiko_external',
                'nilai_konversi_external',
                'score_aspek_kinerja_external',
                'adjusment_score_external',
                'final_score_rmi_external',
            ]);
        });
    }
};