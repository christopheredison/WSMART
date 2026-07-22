<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class KonstruksiSpesifikSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('project_sektors')->truncate();
        DB::table('project_divisis')->truncate();
        $this->enableForeignKeyChecks();

        $now = Carbon::now();

        $projectDivisis = [
            ['id' => 1, 'divisi_name' => 'Divisi 1', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'divisi_name' => 'Divisi 2', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'divisi_name' => 'Divisi 3', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'divisi_name' => 'Divisi 4', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'divisi_name' => 'Divisi 5', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('project_divisis')->insert($projectDivisis);

        $datas = [
            ['id' => 1, 'sektor_name' => 'Apartement', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'sektor_name' => 'Hotel & Resort', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'sektor_name' => 'Rumah Sakit', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'sektor_name' => 'Office', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'sektor_name' => 'Stadion & Fasilitas Negara', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'sektor_name' => 'Gedung Pendidikan', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'sektor_name' => 'Heritage & Museum', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'sektor_name' => 'Industrial', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'sektor_name' => 'Tempat Ibadah', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'sektor_name' => 'Stasiun dan Terminal', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'sektor_name' => 'Perbankan', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'sektor_name' => 'Data Center', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'sektor_name' => 'Komersial(Mall, Auditorium, Ballroom, Rest Area)', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'sektor_name' => 'Struktur', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'sektor_name' => 'Rumah Hunian', 'project_divisi_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'sektor_name' => 'Jalan Tol', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'sektor_name' => 'Jalan Non Tol', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'sektor_name' => 'Jembatan/Fly Over', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'sektor_name' => 'Infrastruktur Bandara', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'sektor_name' => 'Kawasan', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'sektor_name' => 'Tunnel Jalan', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'sektor_name' => 'Sirkuit', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'sektor_name' => 'Mining Infrastructure', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'sektor_name' => 'Bendungan', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 25, 'sektor_name' => 'Irigasi & Rehabilitasi Sungai', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'sektor_name' => 'Dermaga & Jetty', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 27, 'sektor_name' => 'Container Yard', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'sektor_name' => 'Reklamasi', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'sektor_name' => 'Jalan Kereta', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'sektor_name' => 'Tunnel (Bendungan, Jalan)', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'sektor_name' => 'Pengolahan Air Minum', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'sektor_name' => 'Pengolahan Limbah', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'sektor_name' => 'Sipil Umum Pertambangan', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 34, 'sektor_name' => 'Jaringan Pipa', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 35, 'sektor_name' => 'Conveyor & Instalasi Logistik', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 36, 'sektor_name' => 'Jalan Tol', 'project_divisi_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 37, 'sektor_name' => 'Jalan Non Tol', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 38, 'sektor_name' => 'Jembatan/Fly Over', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 39, 'sektor_name' => 'Infrastruktur Bandara', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 40, 'sektor_name' => 'Kawasan', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 41, 'sektor_name' => 'Tunnel Jalan', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 42, 'sektor_name' => 'Sirkuit', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 43, 'sektor_name' => 'Mining Infrastructure', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 44, 'sektor_name' => 'Bendungan', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 45, 'sektor_name' => 'Irigasi & Rehabilitasi Sungai', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 46, 'sektor_name' => 'Dermaga & Jetty', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 47, 'sektor_name' => 'Container Yard', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 48, 'sektor_name' => 'Reklamasi', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 49, 'sektor_name' => 'Jalan Kereta', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 50, 'sektor_name' => 'Tunnel (Bendungan, Jalan)', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 51, 'sektor_name' => 'Pengolahan Air Minum', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 52, 'sektor_name' => 'Pengolahan Limbah', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 53, 'sektor_name' => 'Sipil Umum Pertambangan', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 54, 'sektor_name' => 'Jaringan Pipa', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 55, 'sektor_name' => 'Conveyor & Instalasi Logistik', 'project_divisi_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 56, 'sektor_name' => 'PLTU', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 57, 'sektor_name' => 'PLTGU (Gas Uap)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 58, 'sektor_name' => 'PLTS (Surya)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 59, 'sektor_name' => 'PLTSa (Sampah)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 60, 'sektor_name' => 'PLTB (Bayu)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 61, 'sektor_name' => 'PLTD (Diesel)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 62, 'sektor_name' => 'PLTP (Panas Bumi)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 63, 'sektor_name' => 'PLTMH (Mini Hidro)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 64, 'sektor_name' => 'PLTAL (Arus Laut)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 65, 'sektor_name' => 'PLTMG (Mesin Gas)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 66, 'sektor_name' => 'PLTG (Gas)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 67, 'sektor_name' => 'Transmisi & SKTT (Saluran Kabel Tegangan Tinggi)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 68, 'sektor_name' => 'Processing Plant (Chemical, Pabrik Petrokimia)', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 69, 'sektor_name' => 'Oil & Gas', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 70, 'sektor_name' => 'Industrial & Fasilitas Pendukung', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 71, 'sektor_name' => 'Mining & Fasilitas Pendukung', 'project_divisi_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 72, 'sektor_name' => 'Building Management', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 73, 'sektor_name' => 'Precast', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 74, 'sektor_name' => 'Ready Mix', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 75, 'sektor_name' => 'Rental', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 76, 'sektor_name' => 'Invesment Project', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 77, 'sektor_name' => 'Research and Development', 'project_divisi_id' => 5, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('project_sektors')->insert($datas);
    }
}
