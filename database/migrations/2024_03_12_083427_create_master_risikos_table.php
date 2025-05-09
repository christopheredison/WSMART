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
        Schema::create('master_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('risiko_id');
            $table->string('kategori_risiko_id');
            $table->string('jenis_risiko_id');
            $table->string('peristiwa_risiko_id');
            $table->text('deskripsi_peristiwa_risiko');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_risikos');
    }
};
