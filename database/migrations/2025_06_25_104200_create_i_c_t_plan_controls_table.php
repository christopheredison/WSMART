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
        Schema::create('ict_plan_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_plan_id')->nullable()->constrained('ict_plans')->onDelete('cascade');
            $table->unsignedInteger('key_control_id')->nullable();
            $table->text('key_control')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ict_plan_controls');
    }
};
