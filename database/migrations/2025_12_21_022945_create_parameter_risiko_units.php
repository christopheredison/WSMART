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
        Schema::create('parameter_risiko_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->foreign('risiko_id')->references('id')->on('identifikasi_risikos')->onDelete('cascade');
            $table->text('nama')->nullable();
            $table->text('formula')->nullable();
            $table->text('satuan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_risiko_units');
    }
};
