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
        Schema::create('dimension_aspect_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_dimension_id')
                  ->constrained('sub_dimensions')
                  ->onDelete('cascade');
            $table->decimal('score_dimension', 8, 2)->nullable();
            $table->string('score_dimension_desc')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dimension_aspect_evaluations');
    }
};
