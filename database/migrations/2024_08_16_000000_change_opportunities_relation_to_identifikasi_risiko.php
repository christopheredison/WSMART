<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeOpportunitiesRelationToIdentifikasiRisiko extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tambahkan kolom baru
        Schema::table('opportunities', function (Blueprint $table) {
            $table->unsignedBigInteger('identifikasi_risiko_id')->nullable()->after('unit_risk_monitoring_id');
        });

        // Pindahkan data dari relasi lama ke relasi baru
        DB::statement('
            UPDATE opportunities o
            JOIN unit_risk_monitorings urm ON o.unit_risk_monitoring_id = urm.id
            SET o.identifikasi_risiko_id = urm.identifikasi_risiko_id
            WHERE o.unit_risk_monitoring_id IS NOT NULL
        ');

        // Hapus kolom lama
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['unit_risk_monitoring_id']);
            $table->dropColumn('unit_risk_monitoring_id');
        });

        // Tambahkan foreign key untuk kolom baru
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('identifikasi_risiko_id')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Tambahkan kolom lama
        Schema::table('opportunities', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_risk_monitoring_id')->nullable()->after('identifikasi_risiko_id');
        });

        // Coba kembalikan data (ini tidak akan sempurna karena relasi one-to-many)
        DB::statement('
            UPDATE opportunities o
            JOIN unit_risk_monitorings urm ON o.identifikasi_risiko_id = urm.identifikasi_risiko_id
            SET o.unit_risk_monitoring_id = urm.id
            WHERE o.identifikasi_risiko_id IS NOT NULL
            LIMIT 1
        ');

        // Hapus kolom baru
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['identifikasi_risiko_id']);
            $table->dropColumn('identifikasi_risiko_id');
        });

        // Tambahkan foreign key untuk kolom lama
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('unit_risk_monitoring_id')
                  ->references('id')
                  ->on('unit_risk_monitorings')
                  ->onDelete('cascade');
        });
    }
}