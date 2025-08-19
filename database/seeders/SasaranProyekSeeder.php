<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SasaranProyekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        $data = [
            [
                'costcenter_code' => 'AD0012403J',
                'kpi_desc' => 'Efisiensi RAB di proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AD0012305N',
                'kpi_desc' => 'Number of Fatality',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AD0012401N',
                'kpi_desc' => 'BIM Maturity Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0022306N',
                'kpi_desc' => 'Karya Inovasi yang Diimplementasikan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0022404N',
                'kpi_desc' => 'Laba Bersih di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AC0011703J',
                'kpi_desc' => 'Risk Ratio di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0012401J',
                'kpi_desc' => 'Risk Ratio di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0022501J',
                'kpi_desc' => 'Schedule Health Check',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0021805N',
                'kpi_desc' => 'Risk Ratio di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0012401J',
                'kpi_desc' => 'Efisiensi BTL di proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0022111N',
                'kpi_desc' => 'Risk Ratio di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0032203J',
                'kpi_desc' => 'QHSE Excellence di Proyek masing-masing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'costcenter_code' => 'AB0022405J',
                'kpi_desc' => 'Cashflow Proyek',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        DB::table('sasaran_proyeks')->insert($data);
    }
}
