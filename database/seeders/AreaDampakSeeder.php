<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Traits\DatabaseSeederTrait;
class AreaDampakSeeder extends Seeder
{
    use DatabaseSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // 1. Nonaktifkan FK checks (MySQL)
        $this->disableForeignKeyChecks();

        // 2. Truncate tabel agar data lama hilang
        DB::table('area_dampaks')->truncate();

        // 3. Aktifkan FK checks kembali
        $this->enableForeignKeyChecks();

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
            // … ulangi untuk entries 4–16 …
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

        // 5. Insert semua data
        DB::table('area_dampaks')->insert($data);

    }
}
