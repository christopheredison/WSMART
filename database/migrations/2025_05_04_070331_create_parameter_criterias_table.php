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
        Schema::create('parameter_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('measurement_parameters');
            $table->text('criteria_statement'); //otomatis diisi misal statement parameters kemudian incerment dari 001 ke 002 dst
            $table->tinyInteger('min_score')->nullable(); //score level terisi - 1 dari level kriteria yang diisi
            $table->tinyInteger('max_score')->nullable(); //biasanya pasti 5 tapi nanti bisa berubah
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_criterias');
    }
};
