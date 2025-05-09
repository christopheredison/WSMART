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
        Schema::create('score_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_criteria_id')
                  ->constrained('parameter_criterias')
                  ->onDelete('cascade');
            $table->foreignId('period_id')
                  ->constrained('rmi_periods')
                  ->onDelete('cascade');
            $table->tinyInteger('score')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_criterias');
    }
};
