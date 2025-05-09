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
        Schema::create('project_risk_monitorings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->integer('quarter')->comment('1 : quarter 1, 2 : quarter 2, 3 : quarter 3, 4 : quarter 4');
            $table->decimal('nilai_dampak', 20, 2)->nullable();
            $table->integer('skala_dampak')->nullable();
            $table->string('nilai_probabilitas')->nullable();
            $table->integer('skala_probabilitas_id')->nullable();
            $table->integer('skala_risiko')->nullable();
            $table->string('level_risiko')->nullable();
            $table->decimal('eksposure_risiko', 20, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risk_monitorings');
    }
};
