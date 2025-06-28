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
        Schema::table('loss_events', function (Blueprint $table) {
            // Menambahkan kolom-kolom baru setelah kolom id
            $table->text('nama_kejadian')->nullable()->after('id');
            $table->text('identifikasi_kejadian')->nullable()->after('nama_kejadian');
            $table->unsignedBigInteger('kategori_kejadian_id')->nullable()->after('identifikasi_kejadian');
            $table->integer('sumber_penyebab_kejadian')->nullable()->comment('1: internal, 2:eksternal')->after('kategori_kejadian_id');
            $table->text('penyebab_masalah')->nullable()->after('sumber_penyebab_kejadian');
            $table->text('penanganan_kejadian')->nullable()->after('penyebab_masalah');
            $table->integer('kategori_risiko_bumn')->nullable()->comment('1: Financial, 2: Operational, 3 : Public & Legal')->after('penanganan_kejadian');
            $table->text('penjelasan_kerugian')->nullable()->after('kategori_risiko_bumn');
            $table->integer('kejadian_berulang')->nullable()->after('penjelasan_kerugian');
            $table->integer('frekuensi_kejadian')->nullable()->after('kejadian_berulang');
            $table->text('rencana_mitigasi')->nullable()->after('frekuensi_kejadian');
            $table->text('realisasi_mitigasi')->nullable()->after('rencana_mitigasi');
            $table->text('perbaikan_mendatang')->nullable()->after('realisasi_mitigasi');
            $table->integer('status_asuransi')->nullable()->comment('1: Ya, 0 : Tidak')->after('perbaikan_mendatang');
            $table->bigInteger('nilai_premi')->nullable()->after('status_asuransi');
            $table->bigInteger('nilai_klaim')->nullable()->after('nilai_premi');
            $table->integer('status_risk_register')->nullable()->comment('1: Ya, 0 : Tidak')->after('nilai_klaim');
            $table->string('no_urut_risiko', 255)->nullable()->after('status_risk_register');
            $table->bigInteger('biaya_risiko_inheren')->nullable()->after('no_urut_risiko');
            $table->bigInteger('biaya_upaya_perbaikan')->nullable()->after('biaya_risiko_inheren');
            $table->bigInteger('hasil_perbaikan')->nullable()->after('biaya_upaya_perbaikan');
            $table->integer('tahun')->nullable()->after('hasil_perbaikan');
            $table->text('deskripsi_kejadian')->nullable()->after('tahun');
            $table->integer('jumlah_kejadian')->nullable()->after('deskripsi_kejadian');
            
            // Menambahkan foreign key untuk kategori_kejadian_id
            $table->foreign('kategori_kejadian_id')->references('id')->on('kategori_kejadians')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_events', function (Blueprint $table) {
            //
            // Menghapus foreign key terlebih dahulu
            $table->dropForeign(['kategori_kejadian_id']);
            
            // Menghapus kolom-kolom yang ditambahkan
            $table->dropColumn([
                'nama_kejadian',
                'identifikasi_kejadian',
                'kategori_kejadian_id',
                'sumber_penyebab_kejadian',
                'penyebab_masalah',
                'penanganan_kejadian',
                'kategori_risiko_bumn',
                'penjelasan_kerugian',
                'kejadian_berulang',
                'frekuensi_kejadian',
                'rencana_mitigasi',
                'realisasi_mitigasi',
                'perbaikan_mendatang',
                'status_asuransi',
                'nilai_premi',
                'nilai_klaim',
                'status_risk_register',
                'no_urut_risiko',
                'biaya_risiko_inheren',
                'biaya_upaya_perbaikan',
                'hasil_perbaikan',
                'tahun',
                'deskripsi_kejadian',
                'jumlah_kejadian'
            ]);
        });
    }
};
