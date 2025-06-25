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
        Schema::create('ict_plans', function (Blueprint $table) {
            $table->id();
            $table->text('sasaran_bumn')->nullable();
            $table->unsignedInteger('risiko_id')->nullable();
            $table->integer('type')->nullable()->comment('1: Unit, 2: Proyek');
            $table->text('business_process')->nullable();
            $table->text('metode_pengujian')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ict_plans');
    }
};
