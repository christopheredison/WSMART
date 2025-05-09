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
        Schema::table('unit_types', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('periodes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('sikap_risikos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('skala_dampaks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('skala_probabilitas', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('rencana_kegiatans', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('peristiwa_risikos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('area_dampaks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('kategori_risikos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tcks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('periodes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sikap_risikos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('skala_dampaks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('skala_probabilitas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('rencana_kegiatans', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('peristiwa_risikos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('area_dampaks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kategori_risikos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tcks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
