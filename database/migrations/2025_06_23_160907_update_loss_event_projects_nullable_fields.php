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
        Schema::table('loss_event_projects', function (Blueprint $table) {
            //
            $table->foreignId('peristiwa_risiko_id')->nullable()->change();
            $table->integer('tahun')->nullable()->change();
            $table->foreignId('project_sektor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_event_projects', function (Blueprint $table) {
            //
            $table->foreignId('peristiwa_risiko_id')->nullable(false)->change();
            $table->integer('tahun')->nullable(false)->change();
            $table->foreignId('project_sektor_id')->nullable(false)->change();
        });
    }
};
