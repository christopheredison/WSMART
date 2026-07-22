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
        Schema::create('user_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('rmi_periods')->onDelete('cascade');
            $table->string('status');
            $table->timestamps();
        });
        
        Schema::table('user_answers', function (Blueprint $table) {
            $table->foreignId('user_survey_id')->nullable()->constrained('user_surveys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropForeign(['user_survey_id']);
            $table->dropColumn('user_survey_id');
        });

        Schema::dropIfExists('user_surveys');
    }
};
