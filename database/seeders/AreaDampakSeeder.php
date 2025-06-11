<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AreaDampakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // 1. Nonaktifkan FK checks (MySQL)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Truncate tabel agar data lama hilang
        DB::table('area_dampaks')->truncate();

        // 3. Aktifkan FK checks kembali
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. Baca file SQL
        //    Sesuaikan path jika folder atau nama filenya berbeda
        $path = database_path('seeders/sql/area_dampak.sql');
        $sql   = File::get($path);

        // 5. Jalankan skrip SQL mentah
        DB::unprepared($sql);

    }
}
