<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixProjectRiskMonitoringStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek jumlah data ProjectRiskMonitoring yang akan terdampak
        $count = DB::table('project_risk_monitorings')
            ->where('status', 6)
            ->count();

        if ($count > 0) {
            $this->command->warn("Ditemukan {$count} data 'Project Risk Monitoring' dengan status 6 (Legacy).");
            $this->command->info("Sedang memperbarui status menjadi 100...");

            // Update Massal
            DB::table('project_risk_monitorings')
                ->where('status', 6)
                ->update([
                    'status' => 100,
                    'is_approved' => true
                ]);

            $this->command->info("BERHASIL! {$count} data Project Risk Monitoring telah diperbarui.");
        } else {
            $this->command->info("Tidak ada data Project Risk dengan status 6. Tidak ada perubahan.");
        }
    }
}
