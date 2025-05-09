<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JenisRencanaPerlakuanRisikoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('jenis_rencana_perlakuan_risikos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Waktu saat ini
        $now = Carbon::now();

        $datas = [
            ['id' => 1, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Kecukupan Desain Kontrol', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Efektivitas Pelaksanaan Kontrol', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'jenis_rencana_perlakuan_risiko' => 'Perbaikan Melalui Breakthrough Project', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Kecukupan Desain Kontrol dan Peningkatan Efektivitas Pelaksanaan Kontrol', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Kecukupan Desain Kontrol dan Perbaikan Melalui Breakthrough Project', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Efektivitas Pelaksanaan Kontrol dan dan Perbaikan Melalui Breakthrough Project', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'jenis_rencana_perlakuan_risiko' => 'Peningkatan Kecukupan Desain Kontrol, Peningkatan Efektivitas Pelaksanaan Kontrol, dan Pebaikan Melalui Breakthrough Project', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'jenis_rencana_perlakuan_risiko' => 'Lainnya', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('jenis_rencana_perlakuan_risikos')->insert($datas);
    }
}
