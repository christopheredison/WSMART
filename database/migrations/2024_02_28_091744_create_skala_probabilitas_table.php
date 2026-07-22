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
        Schema::create('skala_probabilitas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('min');
            $table->bigInteger('max');
            $table->string('type_risiko');
            $table->bigInteger('tingkat');
            $table->string('skala');
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skala_probabilitas');
    }
};
