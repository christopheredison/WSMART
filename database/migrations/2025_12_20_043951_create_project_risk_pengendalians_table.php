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
        Schema::create('project_risk_pengendalians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->constrained('project_risk_monitorings')->onDelete('cascade');
            $table->foreignId('parameter_id')->constrained('parameter_risiko_projects')->onDelete('cascade');

            $table->text('rencana_pengendalian')->nullable();
            $table->text('realisasi_pengendalian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risk_pengendalians');
    }
};
