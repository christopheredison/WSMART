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
        Schema::create('jenis_risikos', function (Blueprint $table) {
            $table->id();
            $table->integer('kategori_risiko_id');
            $table->string('title');
            $table->string('deskripsi')->nullable();
            $table->integer('unit_type_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_risikos');
    }
};
