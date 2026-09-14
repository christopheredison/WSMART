<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dampak_risiko_units', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('penyebab_risikos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('parameter_risiko_units', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('key_risk_indicators', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('dampak_risiko_units', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('penyebab_risikos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('parameter_risiko_units', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('key_risk_indicators', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
