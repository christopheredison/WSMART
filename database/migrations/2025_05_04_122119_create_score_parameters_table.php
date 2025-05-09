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
        Schema::create('score_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')
                  ->constrained('rmi_periods')
                  ->onDelete('cascade');
            $table->foreignId('sub_dimension_id')
                  ->constrained('sub_dimensions')
                  ->onDelete('cascade');
            $table->foreignId('parameter_id')
                  ->constrained('measurement_parameters')
                  ->onDelete('cascade');
            $table->tinyInteger('score')->nullable();
            $table->string('score_parameter_desc')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_parameters');
    }
};
