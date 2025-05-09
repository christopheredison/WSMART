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
        Schema::create('perlakuan_penyebab_risikos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penyebab_risiko_id')->nullable();
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
            $table->string('pic')->nullable();
            $table->date('timeline_perlakuan_risiko_start')->nullable();
            $table->date('timeline_perlakuan_risiko_end')->nullable();
            $table->integer('opsi_perlakuan_risiko')->nullable();
            $table->integer('jenis_rencana_perlakuan_risiko')->nullable();
            $table->timestamps();

            $table->foreign('penyebab_risiko_id')->references('id')->on('penyebab_risiko_projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perlakuan_penyebab_risikos', function (Blueprint $table) {
            // Drop foreign key sebelum menghapus tabel
            $table->dropForeign(['penyebab_risiko_id']);
        });
        Schema::dropIfExists('perlakuan_penyebab_risikos');
    }
};
