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
        // 1) Daftar tabel yang akan di-reset (urutan penting untuk FK)
        $tables = [
            'k_r_i_unit_monitorings',
            'perlakuan_penyebab_unit_monitorings',
            'perlakuan_penyebab_risiko_unit_documents',
            'perlakuan_penyebab_risiko_units',
            'key_risk_indicators',
            'kontrol_eksistings',
            'risk_notes',
            'penyebab_risikos',
            'risk_analyses',
            'identifikasi_risikos',
            'data_batch_notes',
            'data_batches',
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
            // Untuk PostgreSQL, gunakan pendekatan yang tidak memerlukan privilege khusus
            foreach ($tables as $tbl) {
                try {
                    // Coba truncate dulu (jika ada privilege)
                    DB::statement(sprintf('TRUNCATE TABLE "%s" RESTART IDENTITY CASCADE;', $tbl));
                    $this->command->line("– Truncated {$tbl} (Postgres)");
                } catch (\Exception $e) {
                    // Jika gagal, gunakan DELETE
                    DB::table($tbl)->delete();
                    $this->command->line("– Deleted all records from {$tbl} (Postgres - fallback)");
                }
            }
        }
        else {
            $this->command->error("❌ Unsupported database driver: {$driver}");
        }

        $this->command->info('✅ Reset selesai.');
    }
}