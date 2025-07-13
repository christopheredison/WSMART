<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //DB::transaction(function () {
            // Tabel risk_analyses
            Schema::table('risk_analyses', function (Blueprint $table) {
                $table->unsignedBigInteger('nilai_dampak')->nullable()->change();
            });

            // Tabel perlakuan_penyebab_risiko_units
            Schema::table('perlakuan_penyebab_risiko_units', function (Blueprint $table) {
                $table->decimal('biaya_perlakuan_risiko', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q1', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q2', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q3', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q4', 20, 2)->nullable()->change();
            });

            // Tabel perlakuan_penyebab_unit_monitorings
            Schema::table('perlakuan_penyebab_unit_monitorings', function (Blueprint $table) {
                $table->decimal('realisasi_biaya_perlakuan_risiko', 20, 2)->nullable()->change();
            });

            // Tabel perlakuan_penyebab_monitorings
            Schema::table('perlakuan_penyebab_monitorings', function (Blueprint $table) {
                $table->decimal('realisasi_biaya_perlakuan_risiko', 20, 2)->nullable()->change();
            });

            // Tabel project_risk_analisas
            Schema::table('project_risk_analisas', function (Blueprint $table) {
                $table->decimal('risk_limit', 20, 2)->nullable()->change();
                $table->decimal('asumsi_nilai_dampak', 20, 2)->nullable()->change();
            });

            // Tabel penyebab_risiko_projects
            Schema::table('penyebab_risiko_projects', function (Blueprint $table) {
                $table->decimal('biaya_perlakuan_risiko', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q1', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q2', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q3', 20, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q4', 20, 2)->nullable()->change();
            });

            // Tabel projects
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('nk_ppn', 20, 2)->nullable()->change();
                $table->decimal('rapt', 20, 2)->nullable()->change();
                $table->decimal('rapk_0_10_rp', 20, 2)->nullable()->change();
                $table->decimal('rapk_30_50_rp', 20, 2)->nullable()->change();
                $table->decimal('rapk_70_90_rp', 20, 2)->nullable()->change();
                $table->decimal('rapk_100_rp', 20, 2)->nullable()->change();
                $table->decimal('rapk', 20, 2)->nullable()->change();
                $table->decimal('nk', 20, 2)->change();
            });
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::table('risk_analyses', function (Blueprint $table) {
                $table->integer('nilai_dampak')->nullable()->change();
            });

            Schema::table('perlakuan_penyebab_risiko_units', function (Blueprint $table) {
                $table->decimal('biaya_perlakuan_risiko', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q1', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q2', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q3', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q4', 15, 2)->nullable()->change();
            });

            Schema::table('perlakuan_penyebab_unit_monitorings', function (Blueprint $table) {
                $table->decimal('realisasi_biaya_perlakuan_risiko', 15, 2)->nullable()->change();
            });

            Schema::table('perlakuan_penyebab_monitorings', function (Blueprint $table) {
                $table->decimal('realisasi_biaya_perlakuan_risiko', 15, 2)->nullable()->change();
            });

            Schema::table('project_risk_analisas', function (Blueprint $table) {
                $table->decimal('risk_limit', 15, 2)->nullable()->change();
                $table->decimal('asumsi_nilai_dampak', 15, 2)->nullable()->change();
            });

            Schema::table('penyebab_risiko_projects', function (Blueprint $table) {
                $table->decimal('biaya_perlakuan_risiko', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q1', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q2', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q3', 15, 2)->nullable()->change();
                $table->decimal('realisasi_biaya_perlakuan_risiko_q4', 15, 2)->nullable()->change();
            });

            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('nk_ppn', 15, 2)->nullable()->change();
                $table->decimal('rapt', 15, 2)->nullable()->change();
                $table->decimal('rapk_0_10_rp', 15, 2)->nullable()->change();
                $table->decimal('rapk_30_50_rp', 15, 2)->nullable()->change();
                $table->decimal('rapk_70_90_rp', 15, 2)->nullable()->change();
                $table->decimal('rapk_100_rp', 15, 2)->nullable()->change();
                $table->decimal('rapk', 15, 2)->nullable()->change();
                $table->decimal('nk', 15, 2)->nullable(false)->change();
            });
        });
    }
};