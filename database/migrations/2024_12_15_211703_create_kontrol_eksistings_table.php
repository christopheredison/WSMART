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
        Schema::create('kontrol_eksistings', function (Blueprint $table) {
            $table->id();
            $table->integer('peristiwa_risiko_id');//berelasi dengan Model Peristiwa Risiko
            $table->text('kontrol_eksisting');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrol_eksistings');
    }
};
