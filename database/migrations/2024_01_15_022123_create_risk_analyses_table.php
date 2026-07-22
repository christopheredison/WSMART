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
        Schema::create('risk_analyses', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');
            $table->string('skala_probabilitas_id')->nullable();
            $table->string('area_dampak')->nullable();
            $table->string('kategori_dampak')->nullable();
            $table->text('deskripsi_dampak')->nullable();
            $table->integer('nilai_dampak')->nullable();
            $table->integer('skala_dampak')->nullable();
            $table->string('nilai_probabilitas')->nullable();
            $table->integer('skala_risiko')->nullable();
            $table->string('level_risiko')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_analyses');
    }
};
