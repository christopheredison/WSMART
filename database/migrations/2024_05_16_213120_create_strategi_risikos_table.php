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
        Schema::create('strategi_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('periode_id');
            $table->integer('unit_id');
            $table->bigInteger('total_anggaran_unit');
            $table->decimal('persentase_risk_tolerance', 5, 2);
            $table->decimal('persentase_risk_appetite', 5, 2);
            $table->decimal('persentase_risk_tolerance_konservatif', 5, 2);
            $table->decimal('persentase_risk_appetite_konservatif', 5, 2);
            $table->decimal('persentase_risk_tolerance_moderat', 5, 2);
            $table->decimal('persentase_risk_appetite_moderat', 5, 2);
            $table->decimal('persentase_risk_tolerance_agresif', 5, 2);
            $table->decimal('persentase_risk_appetite_agresif', 5, 2);
            $table->decimal('value_risk_tolerance', 15, 2);
            $table->decimal('value_risk_appetite', 15, 2);
            $table->decimal('value_risk_tolerance_konservatif', 15, 2);
            $table->decimal('value_risk_appetite_konservatif', 15, 2);
            $table->decimal('value_risk_tolerance_moderat', 15, 2);
            $table->decimal('value_risk_appetite_moderat', 15, 2);
            $table->decimal('value_risk_tolerance_agresif', 15, 2);
            $table->decimal('value_risk_appetite_agresif', 15, 2);
            $table->integer('batas_konservatif')->comment('1: Low, 2: Low To Moderate, 3: Moderate, 4: Moderate To High, 5: High');
            $table->integer('batas_moderat')->comment('1: Low, 2: Low To Moderate, 3: Moderate, 4: Moderate To High, 5: High');
            $table->integer('batas_agresif')->comment('1: Low, 2: Low To Moderate, 3: Moderate, 4: Moderate To High, 5: High');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategi_risikos');
    }
};
