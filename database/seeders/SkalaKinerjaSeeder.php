<?php

namespace Database\Seeders;

use App\Models\SkalaKinerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkalaKinerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks
        //DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tabel
        //DB::table('skala_kinerjas')->truncate();
        
        // Aktifkan kembali foreign key checks
        //DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Hapus data yang ada
        SkalaKinerja::withTrashed()->forceDelete();

        // Reset ID berdasarkan jenis database
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE skala_kinerjas AUTO_INCREMENT = 1');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER SEQUENCE skala_kinerjas_id_seq RESTART WITH 1');
        }
        
        // Data skala kinerja
        $skalaKinerjas = [
            [
                'tingkat' => 'Sangat Baik',
                'deskripsi' => 'Nilai >= 95',
                'min' => 95,
                'max' => null,
            ],
            [
                'tingkat' => 'Baik',
                'deskripsi' => 'Nilai >= 90 dan < 95',
                'min' => 90,
                'max' => 95,
            ],
            [
                'tingkat' => 'Cukup',
                'deskripsi' => 'Nilai >= 80 dan < 90',
                'min' => 80,
                'max' => 90,
            ],
            [
                'tingkat' => 'Kurang',
                'deskripsi' => 'Nilai >= 70 dan < 80',
                'min' => 70,
                'max' => 80,
            ],
            [
                'tingkat' => 'Buruk',
                'deskripsi' => 'Nilai < 70',
                'min' => null,
                'max' => 70,
            ],
        ];
        
        // Insert data
        foreach ($skalaKinerjas as $skalaKinerja) {
            SkalaKinerja::create($skalaKinerja);
        }
    }
}