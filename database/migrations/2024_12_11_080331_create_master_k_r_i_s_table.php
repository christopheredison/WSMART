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
        Schema::create('master_kri', function (Blueprint $table) {
            $table->id();
            $table->string('kri')->nullable();
            $table->string('satuan_kri')->nullable();
            $table->string('batas_aman')->nullable();
            $table->string('batas_waspada')->nullable();
            $table->string('batas_bahaya')->nullable();
            $table->integer('peristiwa_risiko_id');//berelasi dengan model PeristiwaRisiko
            $table->integer('unit_type_id');//berelasi dengan model UnitType
            $table->integer('jenis')->comment('1: Unit, 2: Project');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kri');
    }
};
