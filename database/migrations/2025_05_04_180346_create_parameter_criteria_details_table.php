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
        Schema::create('parameter_criteria_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_criteria_id')
                  ->constrained('parameter_criterias')
                  ->onDelete('cascade');
            $table->text('criteria');
            $table->integer('level')
                  ->comment('1: Initial Phase, 2: Emerging State, 3: Good Practice, 4: Strong Practice, 5: Best Practice');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_criteria_details');
    }
};
