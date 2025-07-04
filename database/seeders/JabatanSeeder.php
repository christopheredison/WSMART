<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        $jabatans = [
            ['code' => '30000000', 'name' => 'KOMISARIS UTAMA'],
            ['code' => '30000001', 'name' => 'KOMISARIS'],
            ['code' => '30000003', 'name' => 'DIREKTUR UTAMA'],
            ['code' => '30000004', 'name' => 'DIREKTUR'],
            ['code' => '30000005', 'name' => 'SENIOR VICE PRESIDENT'],
            ['code' => '30000006', 'name' => 'DIREKTUR UTAMA ANAK PERUSAHAAN'],
            ['code' => '30000007', 'name' => 'DIREKTUR ANAK PERUSAHAAN'],
            ['code' => '30000008', 'name' => 'SENIOR EXPERT 1'],
            ['code' => '30000009', 'name' => 'DIREKTUR ASOSIASI'],
            ['code' => '30000011', 'name' => 'DIREKTUR CUCU PERUSAHAAN'],
            ['code' => '30000013', 'name' => 'CHAIRMAN'],
            ['code' => '30000014', 'name' => 'SENIOR VICE PRESIDENT ANAK PERUSAHAAN'],
            ['code' => '30000016', 'name' => 'PRESIDENT DIRECTOR'],
            ['code' => '30000018', 'name' => 'VICE PRESIDENT'],
            ['code' => '30000019', 'name' => 'VICE PRESIDENT ANAK PERUSAHAAN'],
            ['code' => '30000020', 'name' => 'PARTNER'],
            ['code' => '30000022', 'name' => 'ASSOCIATE DIRECTOR'],
            ['code' => '30000023', 'name' => 'EXECUTIVE DIRECTOR'],
            ['code' => '30000024', 'name' => 'EXECUTIVE COMMISSIONER'],
            ['code' => '30000025', 'name' => 'ASSOCIATE VICE PRESIDENT'],
            ['code' => '30000026', 'name' => 'DEPUTY DIRECTOR'],
            ['code' => '30000027', 'name' => 'COORDINATOR'],
            ['code' => '30000028', 'name' => 'KEPALA SEKSI PROYEK MEGA'],
            ['code' => '30000033', 'name' => 'JUNIOR EXPERT'],
            ['code' => '30000034', 'name' => 'STAFF'],
            ['code' => '30000036', 'name' => 'STAF PROYEK'],
            ['code' => '30000038', 'name' => 'VICE PRESIDENT'],
            ['code' => '30000039', 'name' => 'SENIOR MANAGER'],
            ['code' => '30000064', 'name' => 'KOORDINATOR'],
            ['code' => '31000001', 'name' => 'OFFICER'],
            ['code' => '31000002', 'name' => 'ASSISTANT MANAGER'],
            ['code' => '31000003', 'name' => 'MANAGER'],
            ['code' => '31000004', 'name' => 'SENIOR OFFICER'],
            ['code' => '31000005', 'name' => 'ASSISTANT VICE PRESIDENT'],
            ['code' => '31000006', 'name' => 'VICE PRESIDENT ASSISTANT'],
            ['code' => '31000007', 'name' => 'MANAGER SUBDEPARTMENT'],
            ['code' => '31000008', 'name' => 'MANAGER SUBDIVISION'],
            ['code' => '31000009', 'name' => 'ASSOCIATE MANAGER'],
            ['code' => '31000010', 'name' => 'SENIOR ASSOCIATE MANAGER'],
            ['code' => '31000011', 'name' => 'HEAD OF UNIT'],
            ['code' => '31000012', 'name' => 'HEAD OF DEPARTMENT'],
            ['code' => '31000013', 'name' => 'HEAD OF DIVISION'],
            ['code' => '31000014', 'name' => 'HEAD OF DIRECTORATE'],
            ['code' => '31000015', 'name' => 'HEAD OF SUBDEPARTMENT'],
            ['code' => '31000016', 'name' => 'HEAD OF SUBDIVISION'],
            ['code' => '31000017', 'name' => 'HEAD OF SECTION'],
            ['code' => '31000018', 'name' => 'HEAD OF TEAM'],
            ['code' => '31000019', 'name' => 'SENIOR HEAD OF UNIT'],
            ['code' => '31000020', 'name' => 'SENIOR HEAD OF DEPARTMENT'],
            ['code' => '31000021', 'name' => 'SENIOR HEAD OF DIVISION'],
            ['code' => '31000022', 'name' => 'SENIOR HEAD OF DIRECTORATE'],
            ['code' => '31000023', 'name' => 'SENIOR HEAD OF SUBDEPARTMENT'],
            ['code' => '31000024', 'name' => 'SENIOR HEAD OF SUBDIVISION'],
            ['code' => '31000025', 'name' => 'SENIOR HEAD OF SECTION'],
            ['code' => '31000026', 'name' => 'SENIOR HEAD OF TEAM'],
            ['code' => '31000027', 'name' => 'ASSISTANT HEAD OF UNIT'],
            ['code' => '31000028', 'name' => 'ASSISTANT HEAD OF DEPARTMENT'],
            ['code' => '31000029', 'name' => 'ASSISTANT HEAD OF DIVISION'],
            ['code' => '31000030', 'name' => 'ASSISTANT HEAD OF DIRECTORATE'],
            ['code' => '31000031', 'name' => 'ASSISTANT HEAD OF SUBDEPARTMENT'],
            ['code' => '31000032', 'name' => 'ASSISTANT HEAD OF SUBDIVISION'],
            ['code' => '31000033', 'name' => 'ASSISTANT HEAD OF SECTION'],
            ['code' => '31000034', 'name' => 'ASSISTANT HEAD OF TEAM'],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::updateOrCreate(
                ['code' => $jabatan['code']],
                ['name' => $jabatan['name']]
            );
        }
    }
}
