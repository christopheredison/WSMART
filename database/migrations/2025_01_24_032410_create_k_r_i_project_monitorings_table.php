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
        Schema::create('k_r_i_project_monitorings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kri_project_id');
            $table->unsignedBigInteger('project_monitoring_id');
            $table->integer('status_kri_terkini')->nullable()->comment('1 : Aman, 2 : Waspada, 3 : Bahaya');
            $table->string('nilai_kri_terkini')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('k_r_i_project_monitorings');
    }
};
