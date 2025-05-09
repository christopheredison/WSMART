<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class PeristiwaRisikoSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('peristiwa_risikos')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        // Data yang akan dimasukkan
        $peristiwaRisiko = [
            ['id' => 1, 'kategori_risiko_id' => 1, 'jenis_risiko_id' => 1, 'title' => 'Politik', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 2, 'title' => 'Kenaikan Harga', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 2, 'title' => 'Nilai Tukar Mata Uang', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 2, 'title' => 'Perubahan Suku Bunga', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 3, 'title' => 'Pembayaran/Pendanaan', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 3, 'title' => 'Perpajakan', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 4, 'title' => 'Compliance', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 4, 'title' => 'Tuntutan Hukum', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 9, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 4, 'title' => 'Fraud', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 10, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 5, 'title' => 'Ketidaksesuaian Desain dan Ruang Lingkup Proyek', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 11, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 5, 'title' => 'Kondisi Lapangan Tak Terduga (Unforeseen Site Condition)', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 12, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 5, 'title' => 'Permasalahan Kontrak', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 13, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 5, 'title' => 'Permasalahan Lahan', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 14, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 5, 'title' => 'Ketidaktepatan Pemilihan Mitra (Partner JO/Vendor)', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 15, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 6, 'title' => 'Permasalahan IT & OT', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 16, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 6, 'title' => 'Serangan Siber', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 17, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 7, 'title' => 'Sosial', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 18, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 7, 'title' => 'Kerusakan Lingkungan', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 19, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 7, 'title' => 'Perubahan Cuaca/Iklim', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 20, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Kecelakaan Kesehatan dan Keselamatan Kerja (K3)', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 21, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Engineering Capacity', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 22, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Kualitas Tidak Terpenuhi', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 23, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Gangguan Rantai Pasok & Sumber Daya', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 24, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Force Majeure', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 25, 'kategori_risiko_id' => 2, 'jenis_risiko_id' => 8, 'title' => 'Interface pekerjaan dengan kontraktor lain', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            
        ];

        DB::table('peristiwa_risikos')->insert($peristiwaRisiko);
    }
}
