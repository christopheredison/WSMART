<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixUnitRiskMonitoringStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek jumlah data UnitRiskMonitoring yang akan terdampak
        // Status lama Published = 5
        $count = DB::table('unit_risk_monitorings')
            ->where('status', 5)
            ->count();

        if ($count > 0) {
            $this->command->warn("Ditemukan {$count} data 'Unit Risk Monitoring' dengan status 5 (Legacy Published).");
            $this->command->info("Sedang memperbarui status menjadi 100...");

            // Update Massal
            DB::table('unit_risk_monitorings')
                ->where('status', 5)
                ->update([
                    'status' => 100,
                    'is_approved' => true
                ]);

            $this->command->info("BERHASIL! {$count} data Unit Risk Monitoring telah diperbarui.");
        } else {
            $this->command->info("Tidak ada data Unit Risk Monitoring dengan status 5. Tidak ada perubahan.");
        }
    }
}
