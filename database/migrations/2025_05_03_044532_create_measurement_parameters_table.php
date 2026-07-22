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
        Schema::create('measurement_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_dimension_id')
                  ->constrained('sub_dimensions')
                  ->onDelete('cascade');
            $table->text('statement');
            $table->integer('min_score')->nullable();
            $table->integer('max_score')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurement_parameters');
    }
};
