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
        Schema::create('risk_divisi_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('identifikasi_risiko_id');
            $table->unsignedBigInteger('project_risk_id');
            $table->timestamps();

            $table->foreign('identifikasi_risiko_id')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');

            $table->foreign('project_risk_id')
                  ->references('id')
                  ->on('project_risks')
                  ->onDelete('cascade');

            // Memastikan tidak ada duplikasi relasi
            $table->unique(['identifikasi_risiko_id', 'project_risk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_divisi_projects');
    }
};
