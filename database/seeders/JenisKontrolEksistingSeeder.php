<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class JenisKontrolEksistingSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('jenis_kontrol_eksistings')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        $kontrolEksisting = [
            ['id' => 1, 'jenis_kontrol' => 'Kontrol operasi - level entitas/kantor pusat', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'jenis_kontrol' => 'Kontrol operasi - level operasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'jenis_kontrol' => 'Kontrol kepatuhan (compliance) - level entitas/kantor pusat', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'jenis_kontrol' => 'Kontrol kepatuhan (compliance) - level operasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'jenis_kontrol' => 'Kontrol pelaporan - level entitas/kantor pusat', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'jenis_kontrol' => 'Kontrol pelaporan - level operasi', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('jenis_kontrol_eksistings')->insert($kontrolEksisting);
    }
}
