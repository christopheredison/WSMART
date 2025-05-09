<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class JenisRisikoSeeder extends Seeder
{
    use DatabaseSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('jenis_risikos')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        $jenisRisiko = [
            ['id' => 1, 'kategori_risiko_id' => 1, 'title' => 'Peristiwa Risiko terkait Kebijakan Sektoral', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 2, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Pasar dan Makro Ekonomi', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 3, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Keuangan', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 4, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Hukum, Reputasi dan Kepatuhan', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 5, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa risiko terkait proyek', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 6, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Teknologi Informasi dan Keamanan Siber', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 7, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Sosial dan Lingkungan', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['id' => 8, 'kategori_risiko_id' => 2, 'title' => 'Peristiwa Risiko terkait Operasional', 'deskripsi' => null, 'unit_type_id' => 4, 'sikap_risiko' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('jenis_risikos')->insert($jenisRisiko);

    }
}
