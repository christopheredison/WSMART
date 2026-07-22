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
        Schema::create('perlakuan_penyebab_monitorings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perlakuan_penyebab_id');
            $table->unsignedBigInteger('project_monitoring_id');
            $table->decimal('progress_rencana_perlakuan_risiko', 5, 2)->nullable();
            $table->decimal('realisasi_biaya_perlakuan_risiko', 15, 2)->nullable();
            $table->text('deskripsi_perlakuan_risiko')->nullable();
            $table->text('jenis_program_rkap')->nullable();
            $table->date('timeline_perlakuan_risiko_start')->nullable();
            $table->date('timeline_perlakuan_risiko_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlakuan_penyebab_monitorings');
    }
};
