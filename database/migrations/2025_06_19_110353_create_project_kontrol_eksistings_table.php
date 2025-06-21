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
        Schema::create('project_kontrol_eksistings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_risk_id')->constrained('project_risks');
            $table->string('kontrol_eksisting_desc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_kontrol_eksistings');
    }
};
