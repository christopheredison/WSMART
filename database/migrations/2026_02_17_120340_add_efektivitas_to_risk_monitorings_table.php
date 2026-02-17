<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom ke tabel project_risk_monitorings
        if (Schema::hasTable('project_risk_monitorings')) {
            Schema::table('project_risk_monitorings', function (Blueprint $table) {
                if (!Schema::hasColumn('project_risk_monitorings', 'efektivitas_perlakuan_risiko')) {
                    $table->decimal('efektivitas_perlakuan_risiko', 8, 2)->nullable()->default(0)->comment('Nilai persentase efektivitas');
                }
            });
        }

        // 2. Tambah kolom ke tabel unit_risk_monitorings
        if (Schema::hasTable('unit_risk_monitorings')) {
            Schema::table('unit_risk_monitorings', function (Blueprint $table) {
                if (!Schema::hasColumn('unit_risk_monitorings', 'efektivitas_perlakuan_risiko')) {
                    $table->decimal('efektivitas_perlakuan_risiko', 8, 2)->nullable()->default(0)->comment('Nilai persentase efektivitas');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback project_risk_monitorings
        if (Schema::hasTable('project_risk_monitorings')) {
            Schema::table('project_risk_monitorings', function (Blueprint $table) {
                if (Schema::hasColumn('project_risk_monitorings', 'efektivitas_perlakuan_risiko')) {
                    $table->dropColumn('efektivitas_perlakuan_risiko');
                }
            });
        }

        // Rollback unit_risk_monitorings
        if (Schema::hasTable('unit_risk_monitorings')) {
            Schema::table('unit_risk_monitorings', function (Blueprint $table) {
                if (Schema::hasColumn('unit_risk_monitorings', 'efektivitas_perlakuan_risiko')) {
                    $table->dropColumn('efektivitas_perlakuan_risiko');
                }
            });
        }
    }
};
