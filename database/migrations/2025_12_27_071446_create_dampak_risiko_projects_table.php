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
        Schema::create('dampak_risiko_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->text('dampak_risiko');
            $table->timestamps();

            $table->foreign('risiko_id')->references('id')->on('project_risks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dampak_risiko_projects');
    }
};
