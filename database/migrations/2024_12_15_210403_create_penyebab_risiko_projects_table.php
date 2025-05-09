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
        Schema::create('penyebab_risiko_projects', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id'); //berelasi dengan model ProjectRisk (table project_risks)
            $table->text('penyebab_risiko');
            $table->text('rencana_perlakuan_risiko')->nullable();
            $table->text('output_perlakuan_risiko')->nullable();
            $table->decimal('biaya_perlakuan_risiko', 15, 2)->nullable();
            $table->decimal('progress_rencana_perlakuan_risiko_q1', 5, 2)->nullable();
            $table->decimal('progress_rencana_perlakuan_risiko_q2', 5, 2)->nullable();
            $table->decimal('progress_rencana_perlakuan_risiko_q3', 5, 2)->nullable();
            $table->decimal('progress_rencana_perlakuan_risiko_q4', 5, 2)->nullable();
            $table->decimal('realisasi_biaya_perlakuan_risiko_q1', 15, 2)->nullable();
            $table->decimal('realisasi_biaya_perlakuan_risiko_q2', 15, 2)->nullable();
            $table->decimal('realisasi_biaya_perlakuan_risiko_q3', 15, 2)->nullable();
            $table->decimal('realisasi_biaya_perlakuan_risiko_q4', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyebab_risiko_projects');
    }
};
