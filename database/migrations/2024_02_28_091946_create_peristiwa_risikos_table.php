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
        Schema::create('peristiwa_risikos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kategori_risiko_id');
            $table->bigInteger('jenis_risiko_id')->nullable();
            $table->string('title');
            $table->string('deskripsi')->nullable();
            $table->integer('unit_type_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peristiwa_risikos');
    }
};
