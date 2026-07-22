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
        Schema::create('detail_penilaian_capaian_kinerjas', function (Blueprint $table) {
            $table->id();
            // gantikan foreignId()+constrained() dengan unsignedBigInteger()+foreign()
            $table->unsignedBigInteger('penilaian_capaian_kinerja_id');
            $table->foreign('penilaian_capaian_kinerja_id', 'fk_dpck_penilaian')
                ->references('id')
                ->on('penilaian_capaian_kinerjas')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('parameter_id');
            $table->foreign('parameter_id', 'fk_dpck_parameter')
                ->references('id')
                ->on('parameter_kinerjas')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('option_id');
            $table->foreign('option_id', 'fk_dpck_option')
                ->references('id')
                ->on('pilihan_parameter_kinerjas')
                ->cascadeOnDelete();

            $table->text('comment')->nullable();  // keterangan user
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penilaian_capaian_kinerjas');
    }
};
