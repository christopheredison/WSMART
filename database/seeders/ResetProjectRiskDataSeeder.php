<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ResetProjectRiskDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Daftar tabel yang akan di-reset
        $tables = [
            'project_risks',
            'project_risk_analisas',
            'project_risk_monitorings',
            'project_risk_monitoring_documents',
            'project_risk_rencana_perlakuans',
            'project_periode_lists',
            'penyebab_risiko_projects',
            'loss_event_projects',
        ];

        // 2) Ambil nama koneksi default (sesuai DB_CONNECTION di .env)
        $defaultConn = Config::get('database.default');
        // 3) Dari konfigurasi, cek driver apa yang dipakai
        $driver = Config::get("database.connections.{$defaultConn}.driver");

        $this->command->info("▶ Menjalankan reset pada koneksi [{$defaultConn}] dengan driver [{$driver}]");

        // 4) Berdasarkan driver, jalankan logic masing-masing
        if ($driver === 'mysql') {
            // Disable FK checks, truncate (reset AUTO_INCREMENT), then enable FK checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $tbl) {
                DB::table($tbl)->truncate();
                $this->command->line("– Truncated {$tbl}");
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        elseif ($driver === 'pgsql') {
            // Disable triggers (FK), truncate + restart sequence, lalu enable triggers
            DB::statement('SET session_replication_role = replica;');
            foreach ($tables as $tbl) {
                DB::statement(sprintf(
                    'TRUNCATE TABLE "%s" RESTART IDENTITY CASCADE;',
                    $tbl
                ));
                $this->command->line("– Truncated {$tbl} (Postgres)");
            }
            DB::statement('SET session_replication_role = DEFAULT;');
        }
        else {
            $this->command->error("❌ Unsupported database driver: {$driver}");
        }

        $this->command->info('✅ Reset selesai.');
    }
}
