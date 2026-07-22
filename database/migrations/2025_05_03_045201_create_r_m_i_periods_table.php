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
        Schema::create('rmi_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->decimal('score_rmi', 8, 2)->nullable();
            $table->string('score_rmi_desc')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rmi_periods');
    }
};
