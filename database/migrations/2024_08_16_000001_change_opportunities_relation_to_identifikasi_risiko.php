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

        // Pindahkan data (Sintaks PostgreSQL)
        DB::statement('
            UPDATE opportunities
            SET identifikasi_risiko_id = urm.identifikasi_risiko_id
            FROM unit_risk_monitorings urm
            WHERE opportunities.unit_risk_monitoring_id = urm.id
              AND opportunities.unit_risk_monitoring_id IS NOT NULL
        ');

        // Hapus kolom lama
        Schema::table('opportunities', function (Blueprint $table) {
            // Cek jika foreign key ada sebelum dihapus (best practice)
            $foreignKeys = $this->listTableForeignKeys('opportunities');
            if (in_array('opportunities_unit_risk_monitoring_id_foreign', $foreignKeys)) {
                $table->dropForeign(['unit_risk_monitoring_id']);
            }
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

        // Kembalikan data (Sintaks PostgreSQL dengan subquery untuk 'LIMIT 1')
        // Ini menggunakan subquery berkorelasi, yang valid di PostgreSQL
        DB::statement('
            UPDATE opportunities o
            SET unit_risk_monitoring_id = (
                SELECT urm.id
                FROM unit_risk_monitorings urm
                WHERE urm.identifikasi_risiko_id = o.identifikasi_risiko_id
                LIMIT 1
            )
            WHERE o.identifikasi_risiko_id IS NOT NULL
        ');

        // Hapus kolom baru
        Schema::table('opportunities', function (Blueprint $table) {
            $foreignKeys = $this->listTableForeignKeys('opportunities');
            if (in_array('opportunities_identifikasi_risiko_id_foreign', $foreignKeys)) {
                $table->dropForeign(['identifikasi_risiko_id']);
            }
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

    /**
     * Helper untuk memeriksa foreign key (khususnya berguna untuk PgSQL).
     */
    private function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
}