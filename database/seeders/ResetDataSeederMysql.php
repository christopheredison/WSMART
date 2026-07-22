<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetDataSeederMysql extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder ini khusus untuk MySQL untuk mengosongkan data dan mereset auto-increment
     */
    public function run(): void
    {
        // Daftar tabel yang akan di-reset
        $tables = [
            'approval_logs',
            'data_batches',
            'data_batch_notes',
            'identifikasi_risikos',
            'kamus_risiko_projects',
            'kamus_risiko_units',
            'key_risk_indicators',
            'kontrol_eksistings',
            'k_r_i_projects',
            'k_r_i_project_monitorings',
            'k_r_i_unit_monitorings',
            'loss_events',
            'loss_event_children',
            'loss_event_files',
            'loss_event_projects',
            'monitoring_risikos',
            'monitoring_risiko_files',
            'penyebab_risikos',
            'penyebab_risiko_projects',
            'penyebab_risiko_project_leds',
            'penyebab_risiko_unit_leds',
            'perlakuan_penyebab_monitorings',
            'perlakuan_penyebab_risikos',
            'perlakuan_penyebab_risiko_documents',
            'perlakuan_penyebab_risiko_project_leds',
            'perlakuan_penyebab_risiko_units',
            'perlakuan_penyebab_risiko_unit_documents',
            'perlakuan_penyebab_risiko_unit_leds',
            'perlakuan_penyebab_unit_monitorings',
            'prioritas_risikos',
            'project_kontrol_eksistings',
            'project_risks',
            'project_risk_analisas',
            'project_risk_monitorings',
            'project_risk_monitoring_documents',
            'project_risk_rencana_perlakuans',
            'rencana_kegiatans',
            'rencana_perlakuan_risikos',
            'risk_analyses',
            'risk_contexts',
            'risk_context_members',
            'risk_context_stakeholder_externals',
            'risk_context_stakeholder_internals',
            'risk_notes',
        ];

        $this->command->info("▶ Menjalankan reset data MySQL");

        // Nonaktifkan pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate semua tabel (akan otomatis reset auto-increment)
        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->command->line("– Truncated {$table}");
        }
        
        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Reset data MySQL selesai.');
    }
}