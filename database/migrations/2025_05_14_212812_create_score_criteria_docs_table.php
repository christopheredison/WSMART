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
        Schema::create('score_criteria_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_criteria_id')->constrained('score_criterias')->onDelete('cascade');
            $table->text('filename');
            $table->text('path');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_criteria_docs');
    }
};
