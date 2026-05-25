<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ParameterCriteriaSk8Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tentukan lokasi file SQL yang sudah Anda simpan
        $path = database_path('seeders/sql/sk8_parameter_criteria.sql');

        // Cek apakah file tersebut ada
        if (!File::exists($path)) {
            $this->command->error("File SQL tidak ditemukan di path: {$path}");
            return;
        }

        $this->command->info('Mulai mengeksekusi file SQL...');

        // Ambil isi file SQL
        $sql = File::get($path);

        // Eksekusi raw SQL secara langsung (termasuk BEGIN, TRUNCATE CASCADE, INSERT, dan COMMIT)
        DB::unprepared($sql);

        $this->command->info('Tabel parameter_criterias dan detailnya berhasil di-seed!');
    }
}
