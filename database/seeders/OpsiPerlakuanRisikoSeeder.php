<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OpsiPerlakuanRisikoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('opsi_perlakuan_risikos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Waktu saat ini
        $now = Carbon::now();

        $datas = [
            ['id' => 1, 'opsi_perlakuan_risiko' => 'Transfer/sharing ', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'opsi_perlakuan_risiko' => 'Reduce/mitigate', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'opsi_perlakuan_risiko' => 'Accept/monitor', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'opsi_perlakuan_risiko' => 'Avoid/hindari', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('opsi_perlakuan_risikos')->insert($datas);
    }
}
