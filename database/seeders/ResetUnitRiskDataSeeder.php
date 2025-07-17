<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ResetUnitRiskDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Daftar tabel yang akan di-reset
        $tables = [
            'data_batches',
            'data_batch_notes',
            'identifikasi_risikos',
            'risk_analyses',
            'penyebab_risikos',
            'perlakuan_penyebab_risiko_units',
            'perlakuan_penyebab_risiko_unit_documents',
            'perlakuan_penyebab_unit_monitorings',
            'key_risk_indicators',
            'k_r_i_unit_monitorings',
            'kontrol_eksistings',
            'risk_notes',
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
