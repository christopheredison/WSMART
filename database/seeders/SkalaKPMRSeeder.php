<?php

namespace Database\Seeders;

use App\Models\SkalaKPMR;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkalaKPMRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tabel
        DB::table('skala_kpmrs')->truncate();
        
        // Aktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Data skala KPMR
        $skalaKPMRs = [
            [
                'tingkat' => 'Strong',
                'deskripsi' => 'Nilai >= 91',
                'min' => 91,
                'max' => null,
            ],
            [
                'tingkat' => 'Satisfactory',
                'deskripsi' => 'Nilai >= 85 dan < 91',
                'min' => 85,
                'max' => 91,
            ],
            [
                'tingkat' => 'Fair',
                'deskripsi' => 'Nilai >= 80 dan < 85',
                'min' => 80,
                'max' => 85,
            ],
            [
                'tingkat' => 'Marginal',
                'deskripsi' => 'Nilai >= 75 dan < 80',
                'min' => 75,
                'max' => 80,
            ],
            [
                'tingkat' => 'Unsatisfactory',
                'deskripsi' => 'Nilai < 75',
                'min' => null,
                'max' => 75,
            ],
        ];
        
        // Insert data
        foreach ($skalaKPMRs as $skalaKPMR) {
            SkalaKPMR::create($skalaKPMR);
        }
    }
}