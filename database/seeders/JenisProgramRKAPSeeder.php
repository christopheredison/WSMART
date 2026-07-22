<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class JenisProgramRKAPSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('jenis_program_dalam_rkap')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        $datas = [
            ['id' => 1, 'jenis_program_rkap' => 'Pemasaran dan penjualan', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 2, 'jenis_program_rkap' => 'Pengadaan', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 3, 'jenis_program_rkap' => 'Produksi dan Kualitas Produk', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 4, 'jenis_program_rkap' => 'Teknis dan Teknologi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 5, 'jenis_program_rkap' => 'Keuangan dan Akuntansi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 6, 'jenis_program_rkap' => 'Sistem dan Organisasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 7, 'jenis_program_rkap' => 'Pengembangan SDM', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 8, 'jenis_program_rkap' => 'Penelitian dan Pengembangan', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 9, 'jenis_program_rkap' => 'Pelestarian Lingkungan', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 10, 'jenis_program_rkap' => 'Investasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 11, 'jenis_program_rkap' => 'Lainnya', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
        ];

        DB::table('jenis_program_dalam_rkap')->insert($datas);
    }
}
