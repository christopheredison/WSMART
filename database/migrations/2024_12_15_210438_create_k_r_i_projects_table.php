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
        Schema::create('k_r_i_projects', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');//berelasi dengan model ProjectRisk (table project_risks)
            $table->integer('kri_id'); //berelasi dengan model MasterKRI
            $table->string('kri')->nullable();
            $table->string('satuan_kri')->nullable();
            $table->string('batas_aman')->nullable();
            $table->string('batas_waspada')->nullable();
            $table->string('batas_bahaya')->nullable();
            $table->string('status_kri_terkini_q1')->nullable();
            $table->string('nilai_kri_terkini_q1')->nullable();
            $table->string('status_kri_terkini_q2')->nullable();
            $table->string('nilai_kri_terkini_q2')->nullable();
            $table->string('status_kri_terkini_q3')->nullable();
            $table->string('nilai_kri_terkini_q3')->nullable();
            $table->string('status_kri_terkini_q4')->nullable();
            $table->string('nilai_kri_terkini_q4')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('k_r_i_projects');
    }
};
