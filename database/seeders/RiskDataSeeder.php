<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\Dimension;
use App\Models\SubDimension;
use App\Models\MeasurementParameter;
use App\Traits\DatabaseSeederTrait;

class RiskDataSeeder extends Seeder
{
    use DatabaseSeederTrait;

    public function run()
    {
        // Matikan FK checks & truncate semua tabel
        $this->disableForeignKeyChecks();
        Group::truncate();
        MeasurementParameter::truncate();
        SubDimension::truncate();
        Dimension::truncate();
        $this->enableForeignKeyChecks();

        // 1) Seed groups
        $groups = [
            'Kelompok 1: Dewan Komisaris, Komite, Direksi',
            'Kelompok 2: Kepala Divisi dan Kepala Departemen',
            'Kelompok 3: Risk officer dan fungsional',
        ];
        foreach ($groups as $name) {
            Group::create(['name' => $name]);
        }

        // 2) Struktur dimensions → subdimensions → parameters
        $data = [
            'Budaya dan Kapabilitas Risiko' => [
                'Budaya Risiko' => [
                    'Internalisasi budaya Risiko dalam budaya perusahaan',
                ],
                'Kapabilitas Risiko' => [
                    'Peran Penilaian RMI dalam upaya peningkatan praktik Manajemen Risiko',
                    'Program peningkatan keahlian Risiko',
                ],
            ],
            'Organisasi dan Tata Kelola Risiko' => [
                'Organ Pengelola Risiko' => [
                    'Efektivitas fungsi pengelola risiko',
                    'Tingkat kematangan organ pengelola risiko',
                ],
                'Peran dan Tanggung Jawab Organ Pengelola Risiko' => [
                    'Keterlibatan aktif Dewan Komisaris/Dewan Pengawas dalam pengelolaan Risiko',
                    'Eskalasi permasalahan kepada Dewan Komisaris/Dewan Pengawas',
                    'Tingkat pemahaman Risiko di jajaran Dewan Komisaris/Dewan Pengawas',
                    'Peran komite-komite di bawah Dewan Komisaris/Dewan Pengawas',
                    'Pengurusan aktif Direksi dalam pengelolaan Risiko',
                    'Mandat, wewenang, dan independensi fungsi manajemen risiko untuk memantau semua Risiko',
                    'Efektivitas fungsi pengelola risiko dalam menjalankan tugasnya',
                ],
                'Model Tata Kelola Risiko Tiga Lini dan Tata Kelola Risiko Terintegrasi' => [
                    'Penerapan Model Tata Kelola Risiko Tiga Lini',
                    'Peran dan fungsi Lini Pertama',
                    'Peran dan fungsi Lini Kedua',
                    'Peran dan fungsi Lini Ketiga',
                    'Interaksi antara fungsi Risiko dan Assurance (kepatuhan, legal, audit)',
                    'Peran dan fungsi Tata Kelola Risiko Terintegrasi',
                    'Monitoring risiko entitas induk sampai ke entitas anak',
                ],
            ],
            'Kerangka Risiko dan Kepatuhan' => [
                'Strategi Risiko' => [
                    'Peningkatan kualitas kerangka Manajemen Risiko',
                    'Rencana transformasi Enterprise Risk Management (ERM)',
                    'Peran Manajemen Risiko dalam penyusunan rencana strategis',
                    'Hubungan peran Manajemen Risiko terhadap pencapaian target strategis RKAP',
                    'Kapasitas Risiko',
                    'Selera Risiko',
                    'Komunikasi selera Risiko kepada pemangku kepentingan eksternal',
                ],
                'Kebijakan dan Prosedur' => [
                    'Kebijakan Risiko',
                    'Prosedur Risiko',
                    'Rencana darurat (contingency plan) dalam kondisi terburuk (worst case scenario)',
                    'Reviu & Stress test terhadap prosedur & SOP',
                ],
                'Fungsi Kepatuhan' => [
                    'Organ fungsi kepatuhan dan perannya',
                ],
                'Efektifitas Manajemen Risiko dan Pengendalian Intern' => [
                    'Penerapan Kerangka Integrated Enterprise Risk Management (ERM)',
                    'Efektivitas Pengendalian Intern',
                ],
            ],
            'Proses dan Kontrol Risiko' => [
                'Identifikasi Risiko' => [
                    'Identifikasi Risiko utama',
                ],
                'Pengukuran dan Prioritisasi Risiko' => [
                    'Pengukuran Risiko',
                    'Kerangka proses pengukuran Risiko untuk prioritisasi Risiko',
                    'Integrasi atas seluruh Risiko utama',
                ],
                'Perlakuan Risiko' => [
                    'Aktivitas perlakuan terhadap Risiko utama',
                    'Proses identifikasi dan pengelolaan eksposur Risiko yang berada diatas selera risiko',
                ],
                'Pelaporan Risiko' => [
                    'Pelaporan Risiko melaporkan Risiko secara real-time',
                ],
                'Permodelan Risiko' => [
                    'Permodelan dan Teknologi Risiko',
                ],
                'Data Risiko' => [
                    'Data Risiko',
                ],
            ],
        ];

        // 3) Loop untuk isi dimensions, subdimensions, parameters
        foreach ($data as $dimName => $subs) {
            $dim = Dimension::create(['name' => $dimName]);

            foreach ($subs as $subName => $params) {
                $sub = SubDimension::create([
                    'dimension_id' => $dim->id,
                    'name'         => $subName,
                ]);

                foreach ($params as $stmt) {
                    MeasurementParameter::create([
                        'sub_dimension_id' => $sub->id,
                        'statement'        => $stmt,
                        'min_score'        => 1,
                        'max_score'        => 5,
                    ]);
                }
            }
        }
    }
}
