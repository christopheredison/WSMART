<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KRIProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('master_kri')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Waktu saat ini
        $now = Carbon::now();

        // Data yang akan dimasukkan
        $kriProject = [
            ['id' => 1, 'kri' => 'Perubahan regulasi/kebijakan/Pemilu atau Perubahan pemerintah yang memengaruhi proyek selama proyek berlangsung', 'satuan_kri' => '-', 'batas_aman' => '0', 'batas_waspada' => null, 'batas_bahaya' => null, 'peristiwa_risiko_id' => 1, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'kri' => 'Kenaikan harga item pareto', 'satuan_kri' => '%', 'batas_aman' => '0', 'batas_waspada' => '<1%', 'batas_bahaya' => '>1%', 'peristiwa_risiko_id' => 2, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'kri' => 'Kenaikan Kurs Rupiah', 'satuan_kri' => '%', 'batas_aman' => '0', 'batas_waspada' => '<2%', 'batas_bahaya' => '>2%', 'peristiwa_risiko_id' => 3, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'kri' => 'Kenaikan suku bunga', 'satuan_kri' => '%', 'batas_aman' => '0', 'batas_waspada' => '< 0,5% dari baseline', 'batas_bahaya' => '> 0,5% dari baseline', 'peristiwa_risiko_id' => 4, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'kri' => 'Lama pembayaran termin', 'satuan_kri' => 'hari', 'batas_aman' => '0', 'batas_waspada' => 'Terlambat <14 Hari', 'batas_bahaya' => 'Terlambat > 14 Hari', 'peristiwa_risiko_id' => 5, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'kri' => 'Tidak ada denda atau perubahan tarif pajak dalam 12 bulan terakhir.', 'satuan_kri' => '-', 'batas_aman' => null, 'batas_waspada' => null, 'batas_bahaya' => null, 'peristiwa_risiko_id' => 6, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'kri' => 'Keterlambatan terbitnya izin sesuai schedule', 'satuan_kri' => 'hari', 'batas_aman' => '0', 'batas_waspada' => 'Terlambat <14 Hari', 'batas_bahaya' => 'Terlambat > 14 Hari', 'peristiwa_risiko_id' => 7, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'kri' => 'Gugatan dari pihak luar', 'satuan_kri' => 'jumlah gugatan', 'batas_aman' => '0', 'batas_waspada' => '<5', 'batas_bahaya' => '>5', 'peristiwa_risiko_id' => 8, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'kri' => 'Tidak ada indikasi aktivitas fraud yang terdeteksi dalam 12 bulan terakhir.', 'satuan_kri' => '-', 'batas_aman' => null, 'batas_waspada' => null, 'batas_bahaya' => null, 'peristiwa_risiko_id' => 9, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'kri' => 'Kelengkapan Gambar Desain', 'satuan_kri' => '%', 'batas_aman' => '100%', 'batas_waspada' => '>80%', 'batas_bahaya' => '<80%', 'peristiwa_risiko_id' => 10, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'kri' => 'Perbedaan data tanah awal dengan update', 'satuan_kri' => '%', 'batas_aman' => '0%', 'batas_waspada' => '<5%', 'batas_bahaya' => '>5%', 'peristiwa_risiko_id' => 11, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'kri' => 'Nilai Contract Review', 'satuan_kri' => '%', 'batas_aman' => '>80%', 'batas_waspada' => '70 - 80 %', 'batas_bahaya' => '<70%', 'peristiwa_risiko_id' => 12, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'kri' => 'Persentase lahan bebas', 'satuan_kri' => '%', 'batas_aman' => '100%', 'batas_waspada' => '>80%', 'batas_bahaya' => '<80%', 'peristiwa_risiko_id' => 13, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'kri' => 'Semua mitra menjalankan tugas sesuai komitmen dengan kualitas yang telah disepakati.', 'satuan_kri' => '-', 'batas_aman' => null, 'batas_waspada' => null, 'batas_bahaya' => null, 'peristiwa_risiko_id' => 14, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'kri' => 'Waktu downtime sistem per bulan', 'satuan_kri' => 'jam', 'batas_aman' => '<1 jam', 'batas_waspada' => '>1 jam', 'batas_bahaya' => '>5 jam', 'peristiwa_risiko_id' => 15, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'kri' => 'Percobaan serangan siber selama proyek', 'satuan_kri' => 'kejadian', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 16, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'kri' => 'Tuntutan warga', 'satuan_kri' => 'kali', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 17, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'kri' => 'Laporan pencemaran atau kerusakan lingkungan', 'satuan_kri' => 'kali', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 18, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'kri' => 'Penyimpangan cuaca ekstrim', 'satuan_kri' => '%', 'batas_aman' => '0%', 'batas_waspada' => '<5%', 'batas_bahaya' => '>5%', 'peristiwa_risiko_id' => 19, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'kri' => 'Nearmiss', 'satuan_kri' => 'kali', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 20, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'kri' => 'Tidak ada penyimpangan dalam volume atau kontrak', 'satuan_kri' => '-', 'batas_aman' => null, 'batas_waspada' => null, 'batas_bahaya' => null, 'peristiwa_risiko_id' => 21, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'kri' => 'Jumlah Non-Conformance Report (NCR)', 'satuan_kri' => 'jumlah', 'batas_aman' => '<3', 'batas_waspada' => '3 - 5', 'batas_bahaya' => '>5', 'peristiwa_risiko_id' => 22, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'kri' => 'Keterlambatan Pasokan', 'satuan_kri' => 'jumlah kejadian', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 23, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'kri' => 'Kejadian bencana alam yang memengaruhi proyek', 'satuan_kri' => 'jumlah kejadian', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 24, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 25, 'kri' => 'Keterlambatan akibat kontraktor lain', 'satuan_kri' => 'jumlah kejadian', 'batas_aman' => '0', 'batas_waspada' => '<3 kali', 'batas_bahaya' => '>3 kali', 'peristiwa_risiko_id' => 25, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'kri' => 'Temuan Internal', 'satuan_kri' => 'jumlah temuan', 'batas_aman' => '<3', 'batas_waspada' => '3 - 5', 'batas_bahaya' => '> 5', 'peristiwa_risiko_id' => 7, 'unit_type_id' => 4, 'jenis' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('master_kri')->insert($kriProject);
    }
}
