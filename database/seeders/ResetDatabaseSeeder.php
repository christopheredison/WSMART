<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar tabel yang ingin di-reset
        $tables = [
            'data_batches',
            'identifikasi_risikos',
            'k_r_i_project_monitorings',
            'k_r_i_projects',
            'k_r_i_unit_monitorings',
            'kamus_risiko_aps',
            'kamus_risikos_projects',
            'kamus_risiko_units',
            'key_risk_indicators',
            'kontrol_eksistings',
            'loss_event_ap_files',
            'loss_event_aps',
            'loss_event_files',
            'loss_event_project_files',
            'loss_event_projects',
            'loss_events',
            'metrik_strategi_risikos',
            'notifications',
            'opportunities',
            'parameter_risiko_projects',
            'parameter_risiko_units',
            'penilaian_capaian_kinerjas',
            'penyebab_risiko_ap_leds',
            'penyebab_risiko_project_leds',
            'penyebab_risiko_projects',
            'penyebab_risiko_unit_leds',
            'penyebab_risikos',
            'perlakuan_dampak_monitoring_units',
            'perlakuan_dampak_monitorings',
            'perlakuan_dampak_risiko_documents',
            'perlakuan_dampak_risikos',
            'perlakuan_penyebab_monitorings',
            'perlakuan_penyebab_risiko_ap_leds',
            'perlakuan_penyebab_risiko_documents',
            'perlakuan_penyebab_risiko_project_leds',
            'perlakuan_penyebab_risiko_unit_documents',
            'perlakuan_penyebab_risiko_unit_leds',
            'perlakuan_penyebab_risikos',
            'perlakuan_penyebab_unit_monitorings',
            'project_kontrol_eksistings',
            'project_risk_analisas',
            'project_risk_context_members',
            'project_risk_context_stakeholder_externals',
            'project_risk_context_stakeholder_internals',
            'project_risk_contexts',
            'project_risk_monitoring_documents',
            'project_risk_monitorings',
            'project_risk_pengendalians',
            'project_risk_rencana_perlakuans',
            'project_risks',
            'rencana_perlakuan_risikos',
            'risk_analyses',
            'risk_context_members',
            'risk_context_stakeholder_externals',
            'risk_context_stakeholder_internals',
            'risk_contexts',
            'risk_corporate_ap',
            'risk_corporate_divisi',
            'risk_divisi_projects',
            'risk_monitoring_notes',
            'risk_notes',
            // 'sasarans',
            // 'strategi_bisnis',
            'unit_risk_monitorings',
            'unit_risk_pengendalians',
        ];

        // Filter tabel: Hanya proses tabel yang benar-benar ada di database
        // Ini untuk mencegah error jika ada typo nama tabel
        $existingTables = [];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $existingTables[] = $table;
            } else {
                $this->command->warn("Tabel tidak ditemukan dan dilewati: $table");
            }
        }

        if (empty($existingTables)) {
            $this->command->info("Tidak ada tabel yang di-reset.");
            return;
        }

        // Ubah array menjadi string list table (table1, table2, ...)
        $tableList = implode(', ', $existingTables);

        // Eksekusi TRUNCATE khusus PostgreSQL
        // RESTART IDENTITY: Reset ID sequence ke 1
        // CASCADE: Hapus data di tabel lain yang me-referensi tabel ini (Foreign Key)
        try {
            DB::statement("TRUNCATE TABLE $tableList RESTART IDENTITY CASCADE;");
            $this->command->info('Database berhasil di-reset (Data Cleared & ID Reset).');
        } catch (\Exception $e) {
            $this->command->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
