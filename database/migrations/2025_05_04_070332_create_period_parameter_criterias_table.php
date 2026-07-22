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
        Schema::create('period_parameter_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')
                  ->constrained('rmi_periods')
                  ->onDelete('cascade');
            $table->foreignId('parameter_id')->constrained('measurement_parameters');
            $table->foreignId('criteria_id')->constrained('parameter_criterias');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_parameter_criterias');
    }
};
