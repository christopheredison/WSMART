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
        Schema::create('area_dampak_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_dampak_id');
            $table->text('deskripsi');
            $table->integer('skala')->default(1)->comment('1 : Low, 2 : Low to Moderate, 3 : Moderate, 4 : Moderate to High, 5 : High');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_dampak_details');
    }
};
