<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriRisikoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('kategori_risikos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Waktu saat ini
        $now = Carbon::now();

        // Data yang akan dimasukkan
        $kategoriRisiko = [
            ['id' => 1, 'title' => 'Kategori Risiko Fiskal', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'title' => 'Kategori Risiko Industri Umum', 'deskripsi' => null, 'unit_type_id' => 4, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('kategori_risikos')->insert($kategoriRisiko);
    }
}
