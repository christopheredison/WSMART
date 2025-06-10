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
        Schema::table('strategi_risikos', function (Blueprint $table) {
            //
            // Kolom bigint
            $table->bigInteger('total_anggaran_unit')->nullable()->change();

            // Tipe decimal(5,2)
            $table->decimal('persentase_risk_tolerance', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_appetite', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_tolerance_konservatif', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_appetite_konservatif', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_tolerance_moderat', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_appetite_moderat', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_tolerance_agresif', 5, 2)->nullable()->change();
            $table->decimal('persentase_risk_appetite_agresif', 5, 2)->nullable()->change();

            // Tipe decimal(15,2)
            $table->decimal('value_risk_tolerance', 15, 2)->nullable()->change();
            $table->decimal('value_risk_appetite', 15, 2)->nullable()->change();
            $table->decimal('value_risk_tolerance_konservatif', 15, 2)->nullable()->change();
            $table->decimal('value_risk_appetite_konservatif', 15, 2)->nullable()->change();
            $table->decimal('value_risk_tolerance_moderat', 15, 2)->nullable()->change();
            $table->decimal('value_risk_appetite_moderat', 15, 2)->nullable()->change();
            $table->decimal('value_risk_tolerance_agresif', 15, 2)->nullable()->change();
            $table->decimal('value_risk_appetite_agresif', 15, 2)->nullable()->change();

            // Tipe int
            $table->integer('batas_konservatif')->nullable()->change();
            $table->integer('batas_moderat')->nullable()->change();
            $table->integer('batas_agresif')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategi_risikos', function (Blueprint $table) {
            //
            // Kolom bigint
            $table->bigInteger('total_anggaran_unit')->nullable(false)->change();

            // Tipe decimal(5,2)
            $table->decimal('persentase_risk_tolerance', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_appetite', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_tolerance_konservatif', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_appetite_konservatif', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_tolerance_moderat', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_appetite_moderat', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_tolerance_agresif', 5, 2)->nullable(false)->change();
            $table->decimal('persentase_risk_appetite_agresif', 5, 2)->nullable(false)->change();

            // Tipe decimal(15,2)
            $table->decimal('value_risk_tolerance', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_appetite', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_tolerance_konservatif', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_appetite_konservatif', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_tolerance_moderat', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_appetite_moderat', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_tolerance_agresif', 15, 2)->nullable(false)->change();
            $table->decimal('value_risk_appetite_agresif', 15, 2)->nullable(false)->change();

            // Tipe int
            $table->integer('batas_konservatif')->nullable(false)->change();
            $table->integer('batas_moderat')->nullable(false)->change();
            $table->integer('batas_agresif')->nullable(false)->change();
        });
    }
};
