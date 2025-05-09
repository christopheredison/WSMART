<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class KontrolEksistingSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('kontrol_eksistings')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        // Data yang akan dimasukkan
        $kontrolEksisting = [
            ['id' => 1, 'peristiwa_risiko_id' => 1, 'kontrol_eksisting' => 'SOP Pengelolaan Kontrak Konstruksi & EPC', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'peristiwa_risiko_id' => 2, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'peristiwa_risiko_id' => 3, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'peristiwa_risiko_id' => 4, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'peristiwa_risiko_id' => 5, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'peristiwa_risiko_id' => 6, 'kontrol_eksisting' => 'Prosedur Penerbitan Faktur Pajak', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'peristiwa_risiko_id' => 7, 'kontrol_eksisting' => 'SOP Pengelolaan Kontrak Konstruksi & EPC', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'peristiwa_risiko_id' => 8, 'kontrol_eksisting' => 'WI Penanganan Kasus Hukum Perdata', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 9, 'peristiwa_risiko_id' => 9, 'kontrol_eksisting' => 'Pedoman Sistem Manajemen Anti Penyuapan (SMAP)', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 10, 'peristiwa_risiko_id' => 10, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 11, 'peristiwa_risiko_id' => 11, 'kontrol_eksisting' => 'SOP Pengelolaan Kontrak Konstruksi & EPC', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 12, 'peristiwa_risiko_id' => 12, 'kontrol_eksisting' => 'SOP Pengelolaan Kontrak Konstruksi & EPC', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 13, 'peristiwa_risiko_id' => 13, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 14, 'peristiwa_risiko_id' => 14, 'kontrol_eksisting' => 'SOP Pengadaan Barang dan Jasa Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 15, 'peristiwa_risiko_id' => 15, 'kontrol_eksisting' => 'Pedoman Manajemen Teknologi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 16, 'peristiwa_risiko_id' => 16, 'kontrol_eksisting' => 'Prosedur Manajemen Keamanan Informasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 17, 'peristiwa_risiko_id' => 17, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 18, 'peristiwa_risiko_id' => 18, 'kontrol_eksisting' => 'SOP Manajemen Risiko QHSE', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 19, 'peristiwa_risiko_id' => 19, 'kontrol_eksisting' => 'SOP Manajemen Risiko QHSE', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 20, 'peristiwa_risiko_id' => 20, 'kontrol_eksisting' => 'SOP Manajemen Risiko QHSE', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 21, 'peristiwa_risiko_id' => 21, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 22, 'peristiwa_risiko_id' => 22, 'kontrol_eksisting' => 'BDE Perusahaan', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 23, 'peristiwa_risiko_id' => 23, 'kontrol_eksisting' => 'SOP Pengadaan Barang dan Jasa Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 24, 'peristiwa_risiko_id' => 24, 'kontrol_eksisting' => 'SOP Manajemen Risiko QHSE', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 25, 'peristiwa_risiko_id' => 25, 'kontrol_eksisting' => 'Prosedur Pengendalian Proyek', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 26, 'peristiwa_risiko_id' => 15, 'kontrol_eksisting' => 'SOP Implementasi Building Information Modelling (BIM)', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('kontrol_eksistings')->insert($kontrolEksisting);
    }
}
