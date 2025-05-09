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
        Schema::create('loss_event_projects', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->text('deskripsi_kejadian')->nullable();
            $table->integer('peristiwa_risiko_id');
            $table->integer('project_sektor_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_event_projects');
    }
};
