<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;
class KategoriRisikoSeeder extends Seeder
{
    use DatabaseSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->disableForeignKeyChecks();
        DB::table('kategori_risikos')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        // Data yang akan dimasukkan
        $kategoriRisiko = [
            ['id' => 1, 'title' => 'Risiko Fiskal', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'title' => 'Risiko Kebijakan', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'title' => 'Risiko Komposisi', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'title' => 'Risiko Struktur', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'title' => 'Risiko Restrukturisasi & Reorganisasi', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'title' => 'Risiko Industri Umum', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'title' => 'Risiko Industri Perbankan', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'title' => 'Risiko Industri Asuransi', 'deskripsi' => null, 'unit_type_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('kategori_risikos')->insert($kategoriRisiko);
    }
}
