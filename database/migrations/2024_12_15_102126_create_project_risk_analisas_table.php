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
        Schema::create('project_risk_analisas', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');//berelasi dengan model ProjectRisk (table project_risks)
            $table->integer('skala_probabilitas_id')->nullable(); //berelasi dengan model SkalaProbabilitas (table skala_probabilitas)
            $table->integer('area_dampak')->nullable(); //berelasi dengan model AreaDampak (table area_dampaks)
            $table->string('kategori_dampak')->nullable(); //isian pilihan Finansial atau Non Finansial
            $table->text('deskripsi_dampak')->nullable();
            $table->integer('nilai_dampak')->nullable();
            $table->integer('skala_dampak')->nullable();
            $table->string('nilai_probabilitas')->nullable();
            $table->integer('skala_risiko')->nullable();//dari table risk_map -> nilai_risiko
            $table->string('level_risiko')->nullable();// dari table risk_map -> level_risiko
            $table->decimal('risk_limit', 15, 2)->nullable();//nilai 2,5% dari nilai kontrak
            $table->integer('skala_probabilitas_residual_id')->nullable(); //berelasi dengan model SkalaProbabilitas (table skala_probabilitas)
            $table->integer('nilai_dampak_residual')->nullable();
            $table->integer('skala_dampak_residual')->nullable();
            $table->string('nilai_probabilitas_residual')->nullable();
            $table->integer('skala_risiko_residual')->nullable();//dari table risk_map -> nilai_risiko
            $table->string('level_risiko_residual')->nullable();// dari table risk_map -> level_risiko
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risk_analisas');
    }
};
