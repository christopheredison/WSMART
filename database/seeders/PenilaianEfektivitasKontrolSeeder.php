<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PenilaianEfektivitasKontrolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now();

        $data = [
            ['id' => 1, 'efektivitas_kontrol' => 'Cukup dan Efektif',              'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'efektivitas_kontrol' => 'Cukup dan Efektif Sebagian',     'created_at' => $now->copy()->addSeconds(10), 'updated_at' => $now->copy()->addSeconds(10)],
            ['id' => 3, 'efektivitas_kontrol' => 'Cukup dan Tidak Efektif',        'created_at' => $now->copy()->addSeconds(20), 'updated_at' => $now->copy()->addSeconds(20)],
            ['id' => 4, 'efektivitas_kontrol' => 'Tidak Cukup dan Efektif Sebagian','created_at' => $now->copy()->addSeconds(30), 'updated_at' => $now->copy()->addSeconds(30)],
            ['id' => 5, 'efektivitas_kontrol' => 'Tidak Cukup dan Tidak Efektif',  'created_at' => $now->copy()->addSeconds(40), 'updated_at' => $now->copy()->addSeconds(40)],
        ];

        DB::table('penilaian_efektivitas_kontrols')->insert($data);
    }
}
