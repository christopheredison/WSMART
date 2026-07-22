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
        Schema::create('final_rating_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_rating_id')->constrained('final_ratings');
            $table->integer('bobot')->nullable();
            $table->integer('score_bobot_konversi')->nullable();
            $table->float('total_score_kinerja', 8, 2)->nullable();
            $table->foreignId('rmi_period_id')->constrained('rmi_periods');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_rating_periods');
    }
};
