<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Traits\DatabaseSeederTrait;

class AreaDampakPGSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan FK checks sesuai driver
        $this->disableForeignKeyChecks();

        // Reset data berdasarkan driver (PostgreSQL mendapat perlakuan khusus)
        try {
            if (DB::connection()->getDriverName() === 'pgsql') {
                // Truncate dengan reset identity dan cascade untuk FK
                DB::statement('TRUNCATE TABLE area_dampaks RESTART IDENTITY CASCADE');
            } else {
                // MySQL dan lainnya
                DB::table('area_dampaks')->truncate();
            }
        } catch (\Exception $e) {
            // Fallback: delete dan reset sequence untuk PostgreSQL
            DB::table('area_dampaks')->delete();
            if (DB::connection()->getDriverName() === 'pgsql') {
                try {
                    DB::statement("ALTER SEQUENCE area_dampaks_id_seq RESTART WITH 1");
                } catch (\Exception $seqEx) {
                    // ignore
                }
            }
        }

        // Aktifkan FK checks kembali
        $this->enableForeignKeyChecks();

        // Data area dampak (duplikasi dari AreaDampakSeeder)
        $data = [
            [
                'id'           => 1,
                'title'        => 'Dampak keterlambatan pencapaian program strategis',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Strategis',
                'deskripsi'    => null,
                'created_at'   => '2024-12-22 13:55:40',
                'updated_at'   => '2024-12-22 13:55:40',
                'deleted_at'   => null,
            ],
            [
                'id'           => 2,
                'title'        => 'Pelanggaran hukum',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Hukum',
                'deskripsi'    => null,
                'created_at'   => '2024-12-22 13:55:40',
                'updated_at'   => '2024-12-22 13:55:40',
                'deleted_at'   => null,
            ],
            [
                'id'           => 3,
                'title'        => 'Pelanggaran ketentuan kepatuhan',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Kepatuhan',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 4,
                'title'        => 'Keluhan pelanggan / nasabah / pembeli / supplier',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Reputasi',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 5,
                'title'        => 'Pemberitaan negatif di media',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Reputasi',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 6,
                'title'        => 'Kehilangan daya saing',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Reputasi',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 7,
                'title'        => 'Keluhan karyawan',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Sumber Daya Manusia',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 8,
                'title'        => 'Turn over karyawan bertalenta (regretted turnover)',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Sumber Daya Manusia',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 9,
                'title'        => 'Gangguan aplikasi infrastruktur pendukung',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Sistem Infrastruktur Teknologi dan Keamanan Siber',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 10,
                'title'        => 'Serangan siber',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Sistem Infrastruktur Teknologi dan Keamanan Siber',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 11,
                'title'        => 'Penurunan hasil penilaian platform security',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Sistem Infrastruktur Teknologi dan Keamanan Siber',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 12,
                'title'        => 'Pelanggaran pemenuhan SLA (Service Level Agreement)',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Operasional',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 13,
                'title'        => 'Kasus Pertolongan Pertama',
                'type'         => 'Project',
                'risk_category'=> 'Risiko HSSE dan Sosial',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 14,
                'title'        => 'Fatality',
                'type'         => 'Project',
                'risk_category'=> 'Risiko HSSE dan Sosial',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 15,
                'title'        => 'Kerusakan Lingkungan',
                'type'         => 'Project',
                'risk_category'=> 'Risiko HSSE dan Sosial',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 16,
                'title'        => 'Penurunan ESG rating Sustainalytic',
                'type'         => 'Project',
                'risk_category'=> 'Risiko HSSE dan Sosial',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 17,
                'title'        => 'Penundaan pencairan PMN',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Penyertaan Modal Negara (PMN)',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 18,
                'title'        => 'Total jumlah fraud internal dan eksternal',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Operasional Khusus Industri Perbankan',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 19,
                'title'        => 'Penurunan aset investasi berdasarkan rating surat utang atau peringkat bank penerbit deposito',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Investasi Khusus Industri Asuransi',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
            [
                'id'           => 20,
                'title'        => 'Rasio Klaim',
                'type'         => 'Project',
                'risk_category'=> 'Risiko Aktuaria',
                'deskripsi'    => null,
                'created_at'   => '2025-02-05 03:10:24',
                'updated_at'   => '2025-02-05 03:10:24',
                'deleted_at'   => null,
            ],
        ];

        // Insert data
        DB::table('area_dampaks')->insert($data);

        // Untuk PostgreSQL, set sequence ke nilai max(id)
        if (DB::connection()->getDriverName() === 'pgsql') {
            $maxId = DB::table('area_dampaks')->max('id');
            if ($maxId) {
                DB::statement("SELECT setval('area_dampaks_id_seq', {$maxId}, true)");
            }
        }
    }
}