<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class JenisRisikoSeeder extends Seeder
{
    use DatabaseSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('jenis_risikos')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        $jenisRisiko = [
            // Risiko Fiskal (kategori_risiko_id = 1)
            ['kategori_risiko_id' => 1, 'title' => 'Dividen', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 1, 'title' => 'PMN', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 1, 'title' => 'Subsidi & Kompensasi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Kebijakan (2)
            ['kategori_risiko_id' => 2, 'title' => 'SDM', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 2, 'title' => 'Sektoral', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Komposisi (3)
            ['kategori_risiko_id' => 3, 'title' => 'Konsentrasi Portofolio', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Struktur (4)
            ['kategori_risiko_id' => 4, 'title' => 'Struktur Korporasi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Restrukturisasi & Reorganisasi (5)
            ['kategori_risiko_id' => 5, 'title' => 'Penggabungan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Pengambilalihan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Peleburan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Pemisahan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Pembubaran', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Likuidasi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Kemitraan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 5, 'title' => 'Restrukturisasi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Industri Umum (6)
            ['kategori_risiko_id' => 6, 'title' => 'Formulasi Strategis', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Pasar & Makroekonomi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Keuangan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Reputasi & Kepatuhan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Proyek', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Teknologi & Keamanan Siber', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Sosial & Lingkungan', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 6, 'title' => 'Operasional', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Industri Perbankan (7)
            ['kategori_risiko_id' => 7, 'title' => 'Kredit', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 7, 'title' => 'Likuiditas', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],

            // Risiko Industri Asuransi (8)
            ['kategori_risiko_id' => 8, 'title' => 'Investasi', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['kategori_risiko_id' => 8, 'title' => 'Aktuarial', 'deskripsi' => null, 'unit_type_id' => null, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('jenis_risikos')->insert($jenisRisiko);

    }
}
