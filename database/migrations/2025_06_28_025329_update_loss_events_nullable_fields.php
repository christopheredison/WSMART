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
        Schema::table('loss_events', function (Blueprint $table) {
            //
            // integer fields
            $table->integer('periode_id')->nullable()->change();
            $table->integer('unit_id')->nullable()->change();
            $table->integer('user_id')->nullable()->change();

            // date fields
            $table->date('tanggal_kejadian')->nullable()->change();
            $table->date('rentang_kejadian_awal')->nullable()->change();
            $table->date('rentang_kejadian_akhir')->nullable()->change();

            // varchar/text fields
            $table->string('kategori_risiko_id')->nullable()->change();
            $table->string('jenis_risiko_id')->nullable()->change();
            $table->string('nilai_kerugian_finansial')->nullable()->change();
            $table->string('nilai_kerugian_non_fungsional')->nullable()->change();
            $table->text('peristiwa_kerugian')->nullable()->change();
            $table->string('unit_penanggung_jawab')->nullable()->change();

            // timestamps
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_events', function (Blueprint $table) {
            //
            $table->integer('periode_id')->nullable(false)->change();
            $table->integer('unit_id')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();

            $table->date('tanggal_kejadian')->nullable(false)->change();
            $table->date('rentang_kejadian_awal')->nullable(false)->change();
            $table->date('rentang_kejadian_akhir')->nullable(false)->change();

            $table->string('kategori_risiko_id')->nullable(false)->change();
            $table->string('jenis_risiko_id')->nullable(false)->change();
            $table->string('nilai_kerugian_finansial')->nullable(false)->change();
            $table->string('nilai_kerugian_non_fungsional')->nullable(false)->change();
            $table->text('peristiwa_kerugian')->nullable(false)->change();
            $table->string('unit_penanggung_jawab')->nullable(false)->change();

            $table->timestamp('created_at')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
        });
    }
};
