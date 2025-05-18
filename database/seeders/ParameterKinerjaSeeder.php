<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParameterKinerja;
use App\Models\PilihanParameterKinerja;
use Illuminate\Support\Facades\DB;

class ParameterKinerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // (Optional) Bersihkan data lama jika diperlukan
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ParameterKinerja::truncate();
        PilihanParameterKinerja::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        /*
         * Parameter 1: Capaian KPI Kolegial
         */
        $p1 = ParameterKinerja::create([
            'code'      => '1',
            'name'      => 'Capaian KPI Kolegial',
            'type'=>'capaian',
            'weight'    => 30.00,
            'parent_id' => null,
        ]);

        PilihanParameterKinerja::create(['parameter_id' => $p1->id, 'code' => 'a', 'description' => '>100% target', 'scale' => 4, 'score' => 100]);
        PilihanParameterKinerja::create(['parameter_id' => $p1->id, 'code' => 'b', 'description' => '98 - 100% target',    'scale' => 3, 'score' => 80]);
        PilihanParameterKinerja::create(['parameter_id' => $p1->id, 'code' => 'c', 'description' => '95 - 97% target',     'scale' => 2, 'score' => 65]);
        PilihanParameterKinerja::create(['parameter_id' => $p1->id, 'code' => 'd', 'description' => '<95% target',        'scale' => 1, 'score' => 50]);

        /*
         * Parameter 2: Capaian Kinerja Keuangan (dengan sub)
         */
        $p2 = ParameterKinerja::create([
            'code'      => '2',
            'name'      => 'Capaian Kinerja Keuangan',
            'type'=>'capaian',
            'weight'    => 30.00,
            'parent_id' => null,
        ]);

        $p2a = ParameterKinerja::create([
            'code'      => '2a',
            'name'      => 'Pendapatan',
            'type'=>'capaian',
            'weight'    => 25.00,
            'parent_id' => $p2->id,
        ]);
        PilihanParameterKinerja::create(['parameter_id' => $p2a->id, 'code' => 'a', 'description' => '≥100% target',     'scale' => 3, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id' => $p2a->id, 'code' => 'b', 'description' => '95 - 99% target',     'scale' => 2, 'score' => 70]);
        PilihanParameterKinerja::create(['parameter_id' => $p2a->id, 'code' => 'c', 'description' => '<95% target',        'scale' => 1, 'score' => 50]);

        $p2b = ParameterKinerja::create([
            'code'      => '2b',
            'name'      => 'Total Biaya',
            'type'=>'capaian',
            'weight'    => 25.00,
            'parent_id' => $p2->id,
        ]);
        PilihanParameterKinerja::create(['parameter_id' => $p2b->id, 'code' => 'a', 'description' => '<95% anggaran',     'scale' => 3, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id' => $p2b->id, 'code' => 'b', 'description' => '95 - 100% anggaran',  'scale' => 2, 'score' => 70]);
        PilihanParameterKinerja::create(['parameter_id' => $p2b->id, 'code' => 'c', 'description' => '>100% anggaran',     'scale' => 1, 'score' => 50]);

        $p2c = ParameterKinerja::create([
            'code'      => '2c',
            'name'      => 'Laba Bersih',
            'type'=>'capaian',
            'weight'    => 25.00,
            'parent_id' => $p2->id,
        ]);
        PilihanParameterKinerja::create(['parameter_id' => $p2c->id, 'code' => 'a', 'description' => '≥100% target',     'scale' => 3, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id' => $p2c->id, 'code' => 'b', 'description' => '95 - 99% target',     'scale' => 2, 'score' => 70]);
        PilihanParameterKinerja::create(['parameter_id' => $p2c->id, 'code' => 'c', 'description' => '<95% target',        'scale' => 1, 'score' => 50]);

        $p2d = ParameterKinerja::create([
            'code'      => '2d',
            'name'      => 'Debt to EBITDA',
            'type'=>'capaian',
            'weight'    => 25.00,
            'parent_id' => $p2->id,
        ]);
        PilihanParameterKinerja::create(['parameter_id' => $p2d->id, 'code' => 'a', 'description' => 'Lebih baik dari target', 'scale' => 1, 'score' => 100]);
        PilihanParameterKinerja::create(['parameter_id' => $p2d->id, 'code' => 'b', 'description' => 'Sama dengan target',     'scale' => 2, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id' => $p2d->id, 'code' => 'c', 'description' => 'Kurang dari target',     'scale' => 3, 'score' => 70]);

        /*
         * Parameter 3: Capaian Kinerja Operasi/Produksi Utama
         */
        $p3 = ParameterKinerja::create([
            'code'      => '3',
            'name'      => 'Capaian Kinerja Operasi/Produksi Utama',
            'type'=>'capaian',
            'weight'    => 40.00,
            'parent_id' => null,
        ]);

        PilihanParameterKinerja::create(['parameter_id' => $p3->id, 'code' => 'a', 'description' => '≥100% target',    'scale' => 4, 'score' => 100]);
        PilihanParameterKinerja::create(['parameter_id' => $p3->id, 'code' => 'b', 'description' => '97 - 99% target',   'scale' => 3, 'score' => 80]);
        PilihanParameterKinerja::create(['parameter_id' => $p3->id, 'code' => 'c', 'description' => '94 - 96% target',   'scale' => 2, 'score' => 65]);
        PilihanParameterKinerja::create(['parameter_id' => $p3->id, 'code' => 'd', 'description' => '<94% target',      'scale' => 1, 'score' => 50]);


        // =====================
        // SEEDING KPMR
        // =====================

        // Tahap 1
        $k1 = ParameterKinerja::create([
            'code'=>'1','name'=>'Pencapaian Nilai Eksposur Risiko sesuai dengan target Risiko Residual',
            'type'=>'kpmr','weight'=>30.00,'parent_id'=>null
        ]);
        PilihanParameterKinerja::create(['parameter_id'=>$k1->id,'code'=>'a','description'=>'Nilai Eksposur Risiko lebih rendah dari target','scale'=>3, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id'=>$k1->id,'code'=>'b','description'=>'Nilai Eksposur Risiko sama dengan target','scale'=>2, 'score' => 60]);
        PilihanParameterKinerja::create(['parameter_id'=>$k1->id,'code'=>'c','description'=>'Nilai Eksposur Risiko lebih tinggi dari target','scale'=>1, 'score' => 40]);

        // Tahap 2
        $k2 = ParameterKinerja::create([
            'code'=>'2','name'=>'Pencapaian output pelaksanaan kegiatan perlakuan Risiko sesuai target',
            'type'=>'kpmr','weight'=>20.00,'parent_id'=>null
        ]);
        PilihanParameterKinerja::create(['parameter_id'=>$k2->id,'code'=>'a','description'=>'Terealisasi 90-100%', 'scale'=>5, 'score' => 100]);
        PilihanParameterKinerja::create(['parameter_id'=>$k2->id,'code'=>'b','description'=>'Terealisasi 80-89%',  'scale'=>4, 'score' => 80]);
        PilihanParameterKinerja::create(['parameter_id'=>$k2->id,'code'=>'c','description'=>'Terealisasi 77-79%',  'scale'=>3, 'score' => 60]);
        PilihanParameterKinerja::create(['parameter_id'=>$k2->id,'code'=>'d','description'=>'Terealisasi 60-69%',  'scale'=>2, 'score' => 40]);
        PilihanParameterKinerja::create(['parameter_id'=>$k2->id,'code'=>'e','description'=>'Terealisasi <60%',     'scale'=>1, 'score' => 20]);

        // Tahap 3
        $k3 = ParameterKinerja::create([
            'code'=>'3','name'=>'Realisasi anggaran pelaksanaan kegiatan perlakuan Risiko sesuai anggaran',
            'type'=>'kpmr','weight'=>20.00,'parent_id'=>null
        ]);
        PilihanParameterKinerja::create(['parameter_id'=>$k3->id,'code'=>'a','description'=>'Realisasi biaya sama atau lebih rendah dari anggaran','scale'=>2, 'score' => 80]);
        PilihanParameterKinerja::create(['parameter_id'=>$k3->id,'code'=>'b','description'=>'Realisasi biaya lebih tinggi dari anggaran','scale'=>1, 'score' => 40]);

        // Tahap 4: Ketepatan Penilaian Risiko
        $k4 = ParameterKinerja::create([
            'code'=>'4','name'=>'Ketepatan Penilaian Risiko','type'=>'kpmr','weight'=>30.00,'parent_id'=>null
        ]);
        $sub4a = ParameterKinerja::create(['code'=>'4a','name'=>'Ketepatan Identifikasi Risiko','type'=>'kpmr','weight'=>25.00,'parent_id'=>$k4->id]);
        $sub4b = ParameterKinerja::create(['code'=>'4b','name'=>'Ketepatan Kuantifikasi Risiko','type'=>'kpmr','weight'=>25.00,'parent_id'=>$k4->id]);
        $sub4c = ParameterKinerja::create(['code'=>'4c','name'=>'Ketepatan Rencana Perlakuan Risiko','type'=>'kpmr','weight'=>25.00,'parent_id'=>$k4->id]);
        $sub4d = ParameterKinerja::create(['code'=>'4d','name'=>'Ketepatan Prioritasi Risiko','type'=>'kpmr','weight'=>25.00,'parent_id'=>$k4->id]);
        // Option seeding for sub4 pending; tambahkan opsi jika diperlukan
        // Options for sub-parameter 4a
        PilihanParameterKinerja::create(['parameter_id'=>$sub4a->id,'code'=>'a','description'=>'Tidak ada Risiko baru yang mempengaruhi penurunan kinerja pada triwulan berjalan','scale'=>2, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id'=>$sub4a->id,'code'=>'b','description'=>'Terdapat Risiko baru yang belum teridentifikasi yang mempengaruhi penurunan kinerja pada triwulan berjalan','scale'=>1, 'score' => 50]);

        // Options for sub-parameter 4b
        PilihanParameterKinerja::create(['parameter_id'=>$sub4b->id,'code'=>'a','description'=>'Realisasi perhitungan nilai dampak dan nilai probabilitas memiliki deviasi negatif tidak lebih dari 5%','scale'=>2, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id'=>$sub4b->id,'code'=>'b','description'=>'Realisasi perhitungan nilai dampak dan nilai probabilitas memiliki deviasi negatif lebih dari 5%','scale'=>1, 'score' => 50]);
 
        // Options for sub-parameter 4c
        PilihanParameterKinerja::create(['parameter_id'=>$sub4c->id,'code'=>'a','description'=>'Rencana perlakuan Risiko dapat menurunkan nilai Eksposur Risiko residual sesuai target','scale'=>2, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id'=>$sub4c->id,'code'=>'b','description'=>'Rencana perlakuan Risiko belum dapat menurunkan nilai Eksposur Risiko residual sesuai target','scale'=>1, 'score' => 50]);

        // Options for sub-parameter 4d
        PilihanParameterKinerja::create(['parameter_id'=>$sub4d->id,'code'=>'a','description'=>'Seluruh Risiko di struktur korporasi di bawah BUMN tidak ada yang baru mempengaruhi penurunan kinerja','scale'=>2, 'score' => 90]);
        PilihanParameterKinerja::create(['parameter_id'=>$sub4d->id,'code'=>'b','description'=>'Terdapat Risiko baru di struktur korporasi di bawah BUMN yang tidak masuk dalam Integrasi Risiko yang mempengaruhi penurunan kinerja','scale'=>1, 'score' => 50]);
    }
}
