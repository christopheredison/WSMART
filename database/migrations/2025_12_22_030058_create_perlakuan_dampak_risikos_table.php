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
        Schema::create('perlakuan_dampak_risikos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->text('rencana_perlakuan_risiko');
            $table->text('output_perlakuan_risiko');
            $table->double('biaya_perlakuan_risiko')->default(0);
            $table->string('pic')->nullable();
            $table->unsignedBigInteger('pic_jabatan_id')->nullable();
            $table->json('divisi_terkait')->nullable();
            $table->date('timeline_perlakuan_risiko_start')->nullable();
            $table->date('timeline_perlakuan_risiko_end')->nullable();
            $table->unsignedBigInteger('opsi_perlakuan_risiko')->nullable();
            $table->timestamps();

            $table->foreign('risiko_id')->references('id')->on('project_risks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlakuan_dampak_risikos');
    }
};
